#!/bin/sh

# GoCertdist certificate update script
# This script is executed by cron based on the schedule configured in the web interface

CONFIG_FILE="/usr/local/etc/go-certdist.yaml"
EXECUTABLE="/usr/local/bin/certdist"
LOG_FILE="/var/log/certdist.log"
DOWNLOAD_URL="https://github.com/markus-seidl/go-certdist/releases/latest/download/certdist-freebsd-amd64"

log_msg() {
    echo "$(date -u +"%Y-%m-%dT%H:%M:%SZ"): $1" >> "$LOG_FILE"
}

log_msg "certdist.sh cron job started."

# Check for executable, download if it doesn't exist
if [ ! -f "$EXECUTABLE" ]; then
    log_msg "Executable not found at $EXECUTABLE. Attempting to download..."
    # OPNsense uses fetch by default
    fetch -o "$EXECUTABLE" "$DOWNLOAD_URL"
    if [ $? -eq 0 ]; then
        log_msg "Download successful."
        chmod +x "$EXECUTABLE"
        log_msg "Executable permissions set."
    else
        log_msg "ERROR: Failed to download gocertdist executable from $DOWNLOAD_URL."
        exit 1
    fi
fi

if [ ! -f "$CONFIG_FILE" ]; then
    log_msg "ERROR: Configuration file not found at $CONFIG_FILE"
    exit 1
fi

log_msg "Found executable and config file. Running gocertdist..."

# Run gocertdist and log its output line by line to ensure consistent formatting.
log_msg "Running gocertdist..."
$EXECUTABLE client $CONFIG_FILE 2>&1 | while IFS= read -r line; do
    log_msg "$line"
done
EXIT_CODE_CERTDIST=$?

if [ $? -eq 0 ]; then
    log_msg "gocertdist executed successfully."

    # Check if new certificates were downloaded
    CERT_DIR="/tmp/certdist"
    if [ -d "$CERT_DIR" ] && [ -f "$CERT_DIR/fullchain.pem" ] && [ -f "$CERT_DIR/privkey.pem" ]; then
        log_msg "New certificates found. Updating OPNsense certificate store via configd..."

        # Use configctl to run the import action
        /usr/local/sbin/configctl gocertdist import "$CERT_DIR/fullchain.pem" "$CERT_DIR/privkey.pem"

        if [ $? -eq 0 ]; then
            log_msg "Certificate import completed successfully via configd."
            # Clean up certificate files
            rm -f "$CERT_DIR/*.pem"
            rm -rf "$CERT_DIR"
        else
            log_msg "ERROR: Certificate import failed via configd."
        fi
    else
        log_msg "No new certificates found in $CERT_DIR. Skipping certificate update."
    fi
    
else
    log_msg "ERROR: gocertdist executed with an error (exit code $EXIT_CODE_CERTDIST). Check log for details."
fi

log_msg "certdist.sh cron job finished."

# Add a separator for clarity in the log file
echo "--------------------------------------------------" >> "$LOG_FILE"

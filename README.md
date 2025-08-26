# Go-Certdist OPNsense Plugin

This plugin is in alpha state (aka "works for me"). It's purpose is to update the certificates on OPNsense from a remote go-certdist installation by downloading the certificate with the given age key.

## Installation

Login into OPNsense via the ssh

```bash
fetch  "$DOWNLOAD_URL"

pkg install os-go-certdist-opnsense-devel-1.0_3.pkg
```

Go to the UI and under `Services -> CertDist` you can find the plugin.

## Configuration

Use something like 

```yaml

```

Note that the output directory must be `/tmp/certdist`.

Create a cron job to run the certificate update every night via the OPNsense cron UI.

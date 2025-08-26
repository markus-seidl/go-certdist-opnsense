# Go-Certdist OPNsense Plugin

This plugin is in alpha state (aka "works for me"). It's purpose is to update the certificates on OPNsense from a remote go-certdist installation by downloading the certificate with the given age key.

## Installation

Login into OPNsense via the ssh

```bash
fetch "https://github.com/markus-seidl/go-certdist-opnsense/raw/refs/heads/master/os-go-certdist-opnsense-devel-1.0_3.pkg"

pkg install os-go-certdist-opnsense-devel-1.0_3.pkg
```

Go to the UI and under `Services -> CertDist -> Settings` you can find the plugin.

## Configuration

Use something like 

```yaml
connection:
  server: "YOUR-SERVER-INSTANCE-HTTPS-URL"
age_key:
  private_key: "AGE-SECRET-KEY-..."
certificate:
  - domain: "root domain of the opnsense instance"
    # must be this directory!
    directory: "/tmp/certdist/"
```

Note that the output directory must be `/tmp/certdist`.

Create a cron job to run the certificate update every night via the OPNsense cron UI via 
`System -> Settings -> Cron` and use the command `Run the GoCertdist update script`. 

After the first successful certdist run, you'll find `System -> Trust -> Certificates` a `GoCertdist-Auto` certificate.
The web server should switch to this certificate automagically.

## Troubleshooting

You can execute the update procedure manually via `configctl gocertdist update` the script will create the `/var/log/certdist.log`.


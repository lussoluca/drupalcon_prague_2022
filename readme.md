# Code repository for the talk "Do you know what your Drupal is doing? Observe it" at DrupalCon Prague 2022

## Requirements

- Docker
- [DDEV](https://ddev.com/)

The stack has been tested on macOS and Linux (maybe it should work on Windows wsl2 too, but I haven't tested it).

## Install instructions

### Clone repo
```bash
git clone git@github.com:lussoluca/drupalcon_prague_2022.git
cd drupalcon_prague_2022
```

### Fix folder permission
```bash
chmod 777 .ddev/o11y/grafana/data
chmod 777 .ddev/o11y/prometheus/data
chmod 777 .ddev/o11y/loki/data
```

### Start ddev
```bash
ddev start
```

### Download dependencies
```bash
ddev composer install
```

### Install site
```bash
ddev drush -y si demo_umami --account-pass=admin
```

### Enable relevant modules
```bash
ddev drush -y pm:enable monolog webprofiler o11y_traces o11y_metrics o11y_metrics_requests drupalcon
```

### Login

https://drupalcon-prague-2022.ddev.site/user/login \
username: admin \
password: admin

### Configure o11y_metrics
https://drupalcon-prague-2022.ddev.site/admin/config/system/o11y_metrics/plugins-settings

enable collectors:
* Node count
* User count

enable all node types

### Configure permissions
https://drupalcon-prague-2022.ddev.site/admin/people/permissions#module-o11y_metrics

give permission to anonymous user to "Access site metrics in Prometheus text format."

### Go to dashboard
https://drupalcon-prague-2022.ddev.site:3001

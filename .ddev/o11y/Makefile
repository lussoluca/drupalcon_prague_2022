export_datasource:
	curl -s "https://drupal10.ddev.site:3001/api/datasources" -u admin:admin|jq -c -M '.[]'|split -l 1 - grafana/datasources/

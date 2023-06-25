FROM grafana/grafana-oss:10.0.1

ENV GF_DATASOURCE_PROMETHEUS_URL=http://prometheus:9090

COPY /docker/grafana/provisioning /etc/grafana/provisioning
COPY /docker/grafana/dashboards /var/lib/grafana/dashboards

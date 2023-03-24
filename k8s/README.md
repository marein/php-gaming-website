# k8s Cluster Setup 

### Install k3s
- Log in server via `ssh [IP-ADDRESS]`
- [Optional] Add these lines to your `.bashrc` file:
    - `export INSTALL_K3S_EXEC="--tls-san=[IP-ADDRESS]"`
    - `export INSTALL_K3S_VERSION="v1.21.0+k3s1"`
- Install k3s with:  `curl -sfL [https://get.k3s.io](https://get.k3s.io/) | sh -`
- Copy kube ctx file from `scp [IP-ADDRESS]:/etc/rancher/k3s/k3s.yaml` to your local `~/.kube/config` (or adjust your local kubeconfig)
    - Make sure to change the cluster server to the public IP of your server
- [Optional] Setup increased file watcher limit on your dev machine
    - Check the current inotify file watch limit with `cat /proc/sys/fs/inotify/max_user_watches`
    - Permanently increase it: `sudo sysctl -w fs.inotify.max_user_watches=524288`
- Switch to the new context 

### Install prometheus operator CRDs
    LATEST=$(curl -s https://api.github.com/repos/prometheus-operator/prometheus-operator/releases/latest | jq -cr .tag_name)                                 (dev/markus)
    curl -sL https://github.com/prometheus-operator/prometheus-operator/releases/download/$\{LATEST\}/bundle.yaml | kubectl create -f -
or, alternatively, force `LATEST=v0.63.0` (latest @ 2023.03.24).

### Pre-installed reverse proxy
[//]: # (TODO: Improve this section explaining exactly what to do. I'd need a sandbox and the ability to test/fix stuff before giving precise instructions)
k3s comes with traefik pre-installed. Luckily, it does collect metrics to prometheus by default. 
Yet, it might be relevant to observe the chart setup and maybe reinstall / lock version. 
> Hint: https://artifacthub.io/packages/helm/traefik/traefik

### Create a way to reach the cluster reverse proxy
In order to do that, you would need a domain name resolution to your public address.
Alternatively, you can manipulate your /etc/hosts, and point some ingress addresses to desired IP.

    127.0.0.1 app.php-gaming-website.local
    127.0.0.1 grafana.php-gaming-website.local

# Deploying the HowHotIsMal Stack on Fly.io

This document outlines the steps to deploy and configure the `howhotismal.com` application stack on Fly.io. The project consists of two separate Fly apps:

* `howhotismal-apache`: The public-facing Apache/PHP web server.
* `howhotismal-influxdb`: The backend InfluxDB time-series database.

## Prerequisites

-   You have a [Fly.io](https://fly.io/) account.
-   The `flyctl` command-line tool is installed and you are authenticated (`fly auth login`).

## Step 1: Deploy the InfluxDB Database (`howhotismal-influxdb`)

First, we deploy the backend database.

1.  **Create a Persistent Volume**

    This volume will store the InfluxDB data permanently.

    ```bash
    fly volumes create influxdb_data --size 1 --app howhotismal-influxdb
    ```

2.  **Set Database Secrets**

    Configure the initial database user, organization, and authentication token. **Use your own secure values.**

    ```bash
    fly secrets set --app howhotismal-influxdb \
      DOCKER_INFLUXDB_INIT_MODE=setup \
      DOCKER_INFLUXDB_INIT_USERNAME=my-user \
      DOCKER_INFLUXDB_INIT_PASSWORD="USE_A_VERY_STRONG_PASSWORD" \
      DOCKER_INFLUXDB_INIT_ORG=howhotismal-org \
      DOCKER_INFLUXDB_INIT_BUCKET=howhotismal-bucket \
      DOCKER_INFLUXDB_INIT_ADMIN_TOKEN="USE_A_VERY_SECRET_TOKEN"
    ```

3.  **Deploy the InfluxDB App**

    Deploy the application using its configuration file.

    ```bash
    fly deploy -c influxdb/fly.toml
    ```

## Step 2: Deploy the Apache Web Server (`howhotismal-apache`)

With the database running, we can deploy the public-facing web server.

1.  **Set Web App Secrets**

    Configure the web app with the URL and token needed to connect to the database. Use the same Admin Token you created in the previous step.

    ```bash
    fly secrets set --app howhotismal-apache \
      INFLUXDB_URL=[https://howhotismal-influxdb.fly.dev](https://howhotismal-influxdb.fly.dev) \
      INFLUXDB_TOKEN="THE_VERY_SECRET_TOKEN_YOU_USED_ABOVE" \
      INFLUXDB_ORG=howhotismal-org \
      INFLUXDB_BUCKET=howhotismal-bucket
    ```

2.  **Deploy the Web App**

    ```bash
    fly deploy -c apache/fly.toml
    ```

At this point, the application stack is live and functional on its `.fly.dev` URLs.

## Step 3: Configure Custom Domains

This section maps your custom domains to the running applications.

1.  **Configure `howhotismal.com` for the Web App**
    -   Get the IP addresses for the web app:
        ```bash
        fly ips list --app howhotismal-apache
        ```
    -   Add certificates for the root and `www` subdomains on Fly.io:
        ```bash
        fly certs add --app howhotismal-apache howhotismal.com
        fly certs add --app howhotismal-apache [www.howhotismal.com](https://www.howhotismal.com)
        ```
    -   In your DNS provider, create `A` and `AAAA` records for the root domain (`@`) pointing to the IPs above. Create a `CNAME` record for `www` pointing to `howhotismal-apache.fly.dev.`.

2.  **Configure `influx.howhotismal.com` for the Database**
    -   Get the IP addresses for the database app:
        ```bash
        fly ips list --app howhotismal-influxdb
        ```
    -   Add the certificate on Fly.io:
        ```bash
        fly certs add --app howhotismal-influxdb influx.howhotismal.com
        ```
    -   In your DNS provider, create `A` and `AAAA` records for the `influx` subdomain pointing to the IPs above.

3.  **Update Web App to Use the Final Domain**
    -   After DNS has propagated, update the web app's secret to point to the new, permanent database URL:
        ```bash
        fly secrets set --app howhotismal-apache INFLUXDB_URL=[https://influx.howhotismal.com](https://influx.howhotismal.com)
        ```
    -   Redeploy the web app to apply the change:
        ```bash
        fly deploy -c apache/fly.toml
        ```

## Common Management Commands

-   **Check Application Status:**
    ```bash
    fly status --app <app-name>
    ```

-   **View Live Logs:**
    ```bash
    fly logs --app <app-name>
    ```

-   **List Secrets:**
    ```bash
    fly secrets list --app <app-name>
    ```

-   **Deploy Updates:**
    ```bash
    fly deploy -c <path/to/fly.toml>
    ```


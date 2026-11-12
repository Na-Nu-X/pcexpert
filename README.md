# PC Expert

## 0. Installation & Setup Guide

- Follow these steps to get the development environment running on your local machine

### Linux:

#### 1. Clone the repository

- Open your terminal and clone the project using Git to the XAMPP's projects directory (**/opt/lampp/htdocs**).
`git clone https://github.com/Na-Nu-X/pcexpert.git`

#### 2. Edit httpd-vhosts.conf

- Open **httpd-vhosts.conf** file in **/opt/lampp/etc/extra** directory.
- Add this code.

```apache
<VirtualHost *:80>
    ServerName pcexpert.local
    DocumentRoot "/opt/lampp/htdocs/pcexpert"

    <Directory "/opt/lampp/htdocs/pcexpert">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### 3. Add custom local domain

- Open **hosts** file in **/etc** directory.
- And add this line under other domains: `127.0.0.1 pcexpert.local`.
- Or edit file in terminal if you don't have permissions with `sudo nano /etc/hosts`.

#### 4. Database connection (PostgreSQL) and environment variables

- Create a PostgreSQL database called **pcexpert** and import your .sql dump into it.
- Copy **.env.example** to **.env** in the project root: `cp .env.example .env`.
- Fill in the **POSTGRES_*** values (host, port, database, user, password) and the other keys (mail, reCAPTCHA, Stripe).
- **.env** is listed in **.gitignore** and must never be committed. Credentials are read from it by **Database::connectionDB()** in **src/classes/Database.php**.

#### 5. Edit permissions

- Set appropriate permissions so your IDE can edit files without sudo.
`sudo chown -R $USER:$USER /opt/lampp/htdocs/pcexpert`

#### 6. Appliance of changes

- Restart XAMPP to apply Apache changes: `sudo /opt/lampp/lampp restart`.
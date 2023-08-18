# Introduction

# Requirements
- NodeJS V18
- Python V3
- Pip Python
- Mariadb / Mysql

# Install
- Install NodeJS
  ```
  curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.3/install.sh
  curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.3/install.sh | bash
  source ~/.bashrc
  nvm list-remote
  nvm install v18.17.1
  ``` 
- Install Python3 PIP & dependency (Ubuntu)
  ```
    apt install python3-pip
    pip install mysql-connector-python
    pip install requests
    apt install -y gconf-service libgbm-dev libasound2 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 ca-certificates fonts-liberation libappindicator1 libnss3 lsb-release xdg-utils wget
    apt-get install -y libnss3 libatk1.0-0 libatk-bridge2.0-0 libcups2 libxkbcommon-x11-0 libxcomposite-dev libxdamage1 libxrandr2 libgbm-dev libasound2 libpango-1.0-0 libcairo2
   ```
- Clone repository and Install Library
    ```
    git clone https://github.com/gyulamester/whmcstraccarzap.git
    npm install
    npm update
    ```
- configure for api connect to whmcs database
    nano whmcs/config.py
    ```
    host_db = ''
    name_db = ''
    user_db = ''
    pass_db = ''
    ```
- configure messages to your style
  ```
    nano whmcs/template_message.py
    ```

# Run Service
- Whatsapp BOT & API
start the application and scan the qr code
```
node index.js
```
to start the application automatically, install pm2

to run the python applications to send the whmcs notifications, add the line in the contab
traccar notifications, just add the notifications to traccar, send by sms

# Contrab run py example
 ```
MM HH   * * *   root    /usr/bin/python3 .../whmcs/invoice_unpaid.py
MM HH   * * *   root    /usr/bin/python3 .../whmcs/invoice_duedate.py
MM HH   * * *   root    /usr/bin/python3 .../whmcs/invoice_paid.py
 ```
# Endpoint
- API Endpoint
    ```
    <ip>:8080/api/send
    ```
    Type: POST

    Variable:
    ```
    phone (required)
    message (required)
    ```

    # Extras

  for whmcs to communicate with traccar, download the folder corresponding to the traccar version, and save it in the corresponding whmcs folder

# config traccar.xml notification whatszap
```
   <entry key='notificator.types'>sms</entry>
	 <entry key='notificator.sms.manager.class'>org.traccar.sms.HttpSmsClient</entry>
   <entry key='sms.http.url'>http://127.0.0.1:8080/api/send</entry>
   <entry key='sms.http.template'>
    {"phone": "{phone}","message": "{message}"}
   </entry>
```

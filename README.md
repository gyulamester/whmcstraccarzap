# Module Traccar for whmcs 
# Api whatsapp notifications traccar and whmcs

# Requirements
- NodeJS V18
- Python V3
- Pip Python
- Mariadb or MySQL

# Install NodeJS 

- curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.3/install.sh
- curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.3/install.sh | bash
- source ~/.bashrc
- nvm list-remote
- nvm install v18.17.1
 
# Install Python3 PIP & dependency (Ubuntu)

apt install python3-pip
pip install mysql-connector-python
pip install requests
apt install -y gconf-service libgbm-dev libasound2 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 ca-certificates fonts-liberation libappindicator1 libnss3 lsb-release xdg-utils wget
apt-get install -y libnss3 libatk1.0-0 libatk-bridge2.0-0 libcups2 libxkbcommon-x11-0 libxcomposite-dev libxdamage1 libxrandr2 libgbm-dev libasound2 libpango-1.0-0 libcairo2

# Clone repository and Install Library

git clone https://github.com/gyulamester/whmcstraccarzap.git
npm install
npm update

# configure the file 
nano whmcs/config.py
//to connect to whmcs database

# configure the template for your style and language
 nano whmcs/template_message.py

# configure traccar.xml for sending notifications
    <entry key='notificator.types'>sms</entry>
    <entry key='notificator.sms.manager.class'>org.traccar.sms.HttpSmsClient</entry>
    <entry key='sms.http.url'>http://IP VPS CURRENT PROJECT:8080/api/send</entry>
    <entry key='sms.http.template'>
    {"phone": "{phone}","message": "{message}"}
    </entry>

# run application
node index.js
for automatic start install pm2

I scanned the qr code and mirrored it with your whatsapp

# additional traccar+whmcs modules in traccar versions 4 and 5

download and copy to whmcs /modules/servers folder

 




references: Intprism-Technology/Whatsapp-WHMCS / pedroslopez/whatsapp-web.js/

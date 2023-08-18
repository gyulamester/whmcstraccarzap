import mysql.connector

host_db = '192.185.214.90'
name_db = 'rtopco76_whmc531'
user_db = 'rtopco76_api'
pass_db = '3yifJCg38f4C'

db = mysql.connector.connect(
  host = host_db,
  user = user_db,
  password = pass_db,
  database = name_db
)
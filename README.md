# MyV97DB
PHP API for MySQL Databases

## Headers table
| Parameter | Mandatory | Type | Description |
|-----------|-----------|------|-------------|
|DB_ADDRESS|Y|String|IP address of the db|
|DB_USERNAME|N (if config file is present)|String|Username to access on db|
|DB_PASSWORD|N (if config file is present)|String|Password to access on db|
|DB_SCHEMA|N|String|If passed, default selected schema|

## Example Usage

you need to do a request on the "V97DB/index.php" page.

Request (POST):
```terminal
http://<IP>:<PORT>/V97DB/index.php
```

or, if your php.ini file is a standard one:
```terminal
http://<IP>:<PORT>/V97DB/
```

Headers:
```terminal
db_address:<DB_IP>
```

Body:
```SQL
SELECT column_0, column_1 FROM schema.table WHERE column_1 = 'V97DB'
```

>if the sql queries are only UPDATE or DELETE, you can do multiple queries separated by ";"

Response:
```json
[{"column_0": "1","column_1": "V97DB"}]
```
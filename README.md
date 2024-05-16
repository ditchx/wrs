# wrs

Run using docker:
```sh
docker-compose up -d
```

This will also create a ""mysql" folder mapped to /var/lib/mysql volume to persist the database.


Point your browser to http://localhost:8080/

Access phpmyAdmin as http://localhost:8081/
User: root
Password: rootpassword

Yes, I am comfortable working with legacy code. This should run using PHP version as old as 5.3, but will still able to run on PHP7 and PHP8. Edit the Dockerfile to select the image of the specific PHP version to use and then rebuild using docker-compose.

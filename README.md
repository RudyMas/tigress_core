# Tigress Core
The Core module of the Tigress Framework

## Installation
You can create a new Tigress project by using composer.
````
composer create-project tigress/tigress <project_name>
````

## Documentation

The Core load following modules:

### Automatically loaded
- Twig (Template Engine)

### Manually loaded through the 'config/config.json' file
- Tigress Database (Database Connection)

## JSON Configuration

### config.json

This file contains all the configuration information for the application.

This includes the following:
- Database Connection

### router.json

This file contains all the routes for the application.
The included sample file contains working examples of the following routes:
- GET / (Home Page)
- GET /version (Version Page)
- GET /api/database/get (Database API)
- POST /api/database/post (Database API)
- PUT /api/database/put (Database API)

## Database API
This is a very powerful tool that allows you to interact with the database directly without the need of using the website.
If your not planning to use this feature, you can remove the routes from the 'router.json' file.

This is done through a RESTful API.
The header needs to contain the following:
- Content-Type: application/json
- Username: <username>
- Password: <password>
- Database: <database>

The body needs to contain the following:
- Table: <table>
- Query: <query> (MySQL Query through keybindings)
- Keybindings: <array> (Key + Value to use in the Query)
- Data: <array> (The data for the table, used with POST/PUT)

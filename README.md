Anonymify
===

A tool to anonymize data in a database (**MySQL/MariaDB only**).
<!-- TOC -->
* [Anonymify](#anonymify)
* [Install](#install)
* [Configuration](#configuration)
  * [Configuration file structure](#configuration-file-structure)
* [Anonymization rules](#anonymization-rules)
* [Hints](#hints)
* [Warranty](#warranty)
<!-- TOC -->

# Install
* clone repo
* create .env file
```bash
 cp .env.example .env
 vim .env
```
* install dependency on your system 
```bash
composer install
```
OR 
* for testing, you can run `docker compose up`

# Configuration
Create your own configuration file that must be valid against `data/anonymify.schema.json`.
You can also copy and adapt `data/anonymify.conf.json`.
## Configuration file structure

| Config key        | Description                                                                    | required |
|-------------------|--------------------------------------------------------------------------------|----------|
| "truncate"        | list of columns that should be truncated                                       | yes      |
| "default_masking" | List of columns in all tables that should be masked by default rule (AB****YZ) | yes      |
| "json"            | List of columns and tables that should become an empty json                    | No       |
| "binary_empty"    | List of columns and tables that should become NULL because they're binary      | No       |
| "static_text"     | List of columns that should contain a static text                              | No       |
| "scripts"         | List of sql script to be executed as they are                                  | No       |
| "tables"          | List of tables and it's columns with static values to be set                   | No       |
| "anonymize"       | Definition of anonymize data in columns and tables                             | Yes      |

Example:
```json
{
  "truncate": [
    "queue_history"
  ],
  "default_masking": {
    "name": null,
    "title": [
      "item"
    ]
  },
  "json": {
    "item": [
      "data"
    ]
  },
  "binary_empty": {
    "user_data": [
      "document"
    ]
  },
  "static_text": {
    "document_comment": "Lorem ipsum dolor sit amet"
  },
  "scripts": [
    "./scripts/test.sql"
  ],
  "tables": {
    "user": {
      "street": "Trantow Greens Straße"
    }
  },
  "anonymize": {
    "general": {
      "email": "email",
      "findById": {
        "type": "numbers",
        "args": [
          1000,
          5000
        ]
      },
      "bIC": "bic",
      "domain": "domain",
      "fax": "fax_no",
      "iban": "iban",
      "phone": "phone_no",
      "ip": "ip",
      "vat_id": "vat",
      "blz": "blz"
    },
    "tables": {
      "profile": {
        "email": "email_unique",
        "zip": "zip",
        "country": null,
        "kontonr": "kontonr",
        "name_real": "nomask",
        "company_name": "company_unique"
      }
    }
  }
}

```

# Anonymization rules

| Name            | Description                                                                                 |
|-----------------|---------------------------------------------------------------------------------------------|
| bic             | Generates an unique bank account number                                                     |
| blz             | Generates a random 8 digit number (as on old german BLZ)                                    |
| company_unique  | Generates a random unique company name                                                      |
| default \| null | Masking data, only first and last character is preserved                                    |
| domain          | Generating an unique url                                                                    |
| email           | Generating a safe e-mail address                                                            |
| email_unique    | Generating an unique safe e-mail address                                                    |
| fax_no          | Generating an unique fax number                                                             |
| iban            | Generating an unique german IBAN                                                            |
| ip              | Generating an unique IPv4                                                                   |
| kontonr         | Generating a random old german bank account number (Kontonr.)                               |
| NoMask          | No masking at all, used data as they are                                                    |
| number          | Generating a random number, optional in a range                                             |
| phone_no        | Generating an unique phone number                                                           |
| vat             | Use a predefined VAT number depending on the country identified by the first two characters |

You can write your own rules as following:
```php
//...
use Monolog\Attribute\WithMonologChannel;
//...

#[WithMonologChannel('anonymize')]
class MyRule extends AnonymifyAbstract implements AnonymifyTask {
//do something
}
```
# Hints
The anonymized data will be stored in a temporary table. Therefore, at least the space of the biggest table in tha databases will be used temporary,

# Warranty
No warranty will be given that all rules are working 100% correct.
The usage is on your own risk. Test it well before usage in any production system or before give away the final data. 

# Examples
You can test it by some small example data.

* Import database: `data/db-example.sql`
* Modify your `.env` file for the database connection
* run `bin/console anony -c data/anonymify.config.json`

A simple script for everything (import database, save triggers, run anonymify and restore the triggers) is `anonymify_database.sh`.

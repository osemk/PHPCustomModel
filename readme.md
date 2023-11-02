PHPCustomModel
====

PHPCustomModel let's you to convert your MYSQL database tables to a PHP Model automaticly. This library uses mysqli to connect and to request database.


## FAQ

1. Can I run PHPCustomModel for all MYSQL tables?
    - I tried to adopt it to use for all MYSQL tables but it can gives error with tables with non-PRI keys or tables has less identifier columns. 


## Getting Started

Before you start using this Library, you **need** to know how PHP works, you need to know how MYSQL work and what is Models. This is a fundamental requirement before you start. Without this knowledge, you will only suffer.

### Requirements

- [PHP 7.3](https://php.net) or higher 
- [`mysqli`](https://www.php.net/manual/tr/book.mysqli.php)


### Installing PHPCustomModel

PHPCustomModel is installed using [Composer](https://getcomposer.org).

1. Run `composer require osemk/php-custom-model`. This will install the latest stable release.
2. Include the Composer autoload file at the top of your main file:
	- `include __DIR__.'/vendor/autoload.php';`
3. Make models!

### Basic Example

```php
<?php

include __DIR__.'/vendor/autoload.php';

use CustomModel\CustomModel;

define("VT_HOST","localhost"); // database host
define("VT_ADI","databasename"); //database name
define("VT_KULLANICI","databaseuser"); //databae user
define("VT_SIFRE","databasepassword"); //database password

$model = new CustomModel(TABLENAME,ID);
```


## Documentation

PHPCustomModel converts tables to models. A sample database table that name is "employees"


| id   | firstname | jobtitle             |
| ---- |:---------:| --------------------:|
| 1    | Diane     | President            |
| 2    | Mary      | VP Sales             |
| 3    | Jeff      | VP Marketing         |
| 4    | William   | Sales Manager (APAC) |
| 5    | Gerard    | Sale Manager (EMEA)  |

Using PHPCustomModel we can easily convert this table to a model and we can easily do CRUD processes on table like below.

## Bring table
 $employee = new CustomModel("employees", 1); 

This will fetch row with ID =1 and assign columns to $employee var.

We can check if has a record with ID=1, 

## Check records
$employee->hasRecord(); // if true id=1 founded, if false there is no record.

Now the current object includes these;
`` id=1, firstname=Diane, jobtitle=President ``

We can change these parameters using model easily like below;

## Change parameters
$employee->firstname = "Angel";
$employee->jobtitle = "New President";

or

``
$employee->veri['firstname'] = "Angel";
$employee->veri['jobtitle'] = "New President";
``
and update it;


## Update
$employee->update();

Now our table looks like below;

``
+------+-----------+----------------------+
| id   | firstname | jobtitle             |
+------+-----------+----------------------+
| 1    | Angel     | New President        |
| 2    | Mary      | VP Sales             |
| 3    | Jeff      | VP Marketing         |
| 4    | William   | Sales Manager (APAC) |
| 5    | Gerard    | Sale Manager (EMEA)  |
``
That is it!

If you want to delete record, use;

## Delete
$employee->delete(); 

Now our table;

``
+------+-----------+----------------------+
| id   | firstname | jobtitle             |
+------+-----------+----------------------+
| 2    | Mary      | VP Sales             |
| 3    | Jeff      | VP Marketing         |
| 4    | William   | Sales Manager (APAC) |
| 5    | Gerard    | Sale Manager (EMEA)  |
``
That is awesome!

## Insert New record
Inserting a new row is also easy, just don't send an ID like below,

$employee = new CustomModel("employees",0);

It creates new model from scracth. Now you can add some strings

$employee->firstname = "Onur";
$employee->jobtitle = "King of the World";
$employee->insert();

Now our table look like below;

``
+------+-----------+----------------------+
| id   | firstname | jobtitle             |
+------+-----------+----------------------+
| 2    | Mary      | VP Sales             |
| 3    | Jeff      | VP Marketing         |
| 4    | William   | Sales Manager (APAC) |
| 5    | Gerard    | Sale Manager (EMEA)  |
| 6    | Onur      | King of the World    |
``

## Other fetching methods

You can fetch directly using other columns except ids.

$employee = new CustomModel("employees",['firstname' => 'Onur']); it directly fetches id=6 but this column is not an identifier may be there are some records too, so it fetches only latest "Onur" record. 

So you can make more unique your request with adding extra columns like below;
$employee = new CustomModel("employees",['firstname' => 'Onur', 'jobtitle' => 'King of the World']);

## Other easy method

The best method for this library is save() method. When you want to change or insert a record to table, you can use save().

$employee = new CustomModel("employees", 7); 
$employee->firstname = "Ali";
$employee->jobtitle = "Amele";

$employee->save();
// if there is id=7 currently it updates record 7; if there is no id=7 record, it inserts automaticly to id=7.

So awesome, didn't it?

## License

MIT License, &copy; Onur Erginer 2023-present.
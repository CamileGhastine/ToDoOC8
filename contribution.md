#CONTRIBUTION

##I)	Project installation
1) Open the command line interface (CLI) and choose a folder to install the project.
2) Download the project with the command:  
   `git clone https://github.com/CamileGhastine/ToDoOC8.git`
3) Go to the project folder with the command:  
   `cd ToDoOC8`
4) Install composer and its dependencies with the command:  
   `compose install`
5) Open the .env file and configure the connection to the database (line 26)
6) Create the database and load the fixtures with the command:  
   `Compose prepare`  
   
Before starting coding, create your own git branch, naming it with a meaningful name, reflecting the functionality developed:  
`git checkout -b nameOfMyBranch`


##II)	Contribution to the project
###1)	Development of the new functionality
1) Write your code respecting the standards set out below.
2) MANDATORY write (before, during or after) unit and functional tests and try to cover:
    * all the code,
    * the maximum of borderline cases.
3) Perform relevant commits with clear messages:  
   `git commit -am "my message"`
4) Push your commits to the remote github repository:  
   `git push origin nameOfMyBranch`


**Code standard:**  
In order to facilitate collaborative work, your code must follow a few rules:
* Classes and methods must be commented out.  
Without abusing it, do not hesitate to comment on certain parts of the code if it seems necessary to you.
* Your code must be well presented and well indented.  
If necessary, install PHP-CS-Fixer:  
`composer global require friendsofphp/php-cs-fixer`  
And automatically fix problems with the command:  
`php-cs-fixer fix src/myFolder/myFile.php`
* Your code must meet Symfony code standards based on PSR-1, PSR-2, PSR-4 and PSR-12 (https://symfony.com/doc/4.4/contributing/code/standards.html)  
If necessary, install PHP-CodeSniffer:  
`composer global require "squizlabs/php_codesniffer=*"`  
And correct for each standard the problems raised by PHP-CodeSniffer:  
`phpcbf --standard=PSR12 src/myFolder/myFile`

**Important:** In order not to complicate code proofreading, never correct the standards of a code that you have not written.

###2)	Merge your modifications

1)	If this seems useful, do not hesitate to write a documentation on the developed functionality.

2)	Check code standards, quality and performance of code (see below).

3) Check that all tests (existing ones and yours) turn green.
   To do this, run the command:  
   `bin/phpunit`

4) Submit a pull request:
    * On the repository of your Github account, click on "Compare & pull request".
    * Complete the form by choosing a judicious title and giving clear explanations of the changes made.
    * Validate the pull request.

Your proposed modifications will be examined. If they are deemed appropriate, they will be incorporated into the project.

**Code quality:**  
Use CodeClimat (www.codeclimat.com) or Codacy (www.codacy.com ) to submit your code for quality control.  
If necessary, modify your code in order to resolve the problems raised.

**Code performance:**  
Use Blackfire (https://www.blackfire.io/) to submit your code for performance check.  
In case of performance degradation, find optimization solutions to resolve this.

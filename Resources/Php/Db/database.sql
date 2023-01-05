/*Create database*/
Create Database BikeMaster Character Set utf8 Collate utf8_general_ci;
/*Table of users*/
Create Table BikeMaster.Users (
  	ID int
    	Unsigned
    	Primary Key
    	Auto_Increment
    	Not Null,
    Username varchar(24)
		Unique
    	Not Null,
    Email varchar(256)
		Unique
    	Not Null,
    Password varchar(255)
    	Not Null,
    RegistrationTime int Unsigned Default Unix_Timestamp() Not Null
);
/*Table of reasonIDs*/
Create Table BikeMaster.ReasonIDs (
  	ID int
    	Unsigned
    	Primary Key
    	Auto_Increment
    	Not Null,
    NameID varchar(32)
		Unique
    	Not Null
);
/*Table of settings*/
Create Table BikeMaster.Settings(
  	ID int
    	Unsigned
    	Primary Key
    	Auto_Increment
    	Not Null,
    NameID varchar(32)
		Unique
    	Not Null,
    Value varchar(64)
    	Not Null
);

/*Inserts*/
/*ReasonIDs*/
Insert Into BikeMaster.ReasonIDs (NameID) Values
	('DatabaseError'),
	('Accepted'),
	('NoPost'),
	('NoGet'),
	('IsNull'),
	('InvalidType'),
	('InvalidInputs'),
	('InvalidCredentials'),
	('NotLoggedIn'),
	('InvalidLogin'),
	('TimedOut'),
	('UserNotFound'),
	('UsernameTaken'),
	('UsernameNotTaken'),
	('InvalidCharacters'),
	('TooManyUnderscores'),
	('TooShort'),
	('TooLong'),
	('InvalidEmail'),
	('EmailTaken'),
	('EmailNotTaken'),
	('TooWeak'),
	('DoNotMatch'),
	('NoReasonAvailable');
/*Settings*/
Insert Into BikeMaster.Settings (NameID, Value) Values ('ProjectName', 'BikeMaster');
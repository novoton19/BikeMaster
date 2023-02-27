/*Create database*/
Create Database BikeMaster Character Set utf8 Collate utf8_general_ci;
/*Table of users*/
Create Table BikeMaster.Users(
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
	ProfilePictureUrl varchar(255),
	Description varchar (511),
	 /*-90-90 deg. Precision 3 decimal places (111.1m)*/
    Latitude double(6, 3),
    /*-180-180 deg. Precision 3 decimal places (111.1m)*/
    Longitude double(7, 3),
    RegistrationTime int
		Unsigned
		Default Unix_Timestamp()
		Not Null
);
/*Friend relations*/
Create Table BikeMaster.FriendRelations(
	ID int
    	Unsigned
    	Primary Key
    	Auto_Increment
    	Not Null,
    SenderUserID int
    	Unsigned
    	Not Null,
    ReceiverUserID int 
    	Unsigned 
    	Not Null,
    Accepted tinyint(1)
    	Default 0
    	Not Null,
    RequestTime int
    	Unsigned
    	Default Unix_Timestamp()
    	Not Null,
    AcceptTime int 
    	Unsigned,
    
    Foreign Key (SenderUserID)
    	References BikeMaster.Users(ID),
    Foreign Key (ReceiverUserID)
    	References BikeMaster.Users(ID)
);
/*Table of notification types*/
Create Table BikeMaster.NotificationTypes(
  	ID int
    	Unsigned
    	Primary Key 
    	Auto_Increment 
    	Not Null,
    NameID varchar(24)
		Unique
    	Not Null
);
/*Table of notifications*/
Create Table BikeMaster.Notifications(
	ID int
    	Unsigned
    	Primary Key 
    	Auto_Increment 
    	Not Null,
	TypeID int
		Unsigned
		Not Null,
	TargetUserID int
		Unsigned
		Not Null,
	TriggerUserID int
		Unsigned
		Default Null,
	Viewed tinyint(1)
		Default 0
		Not Null,
	TriggerTime int
		Unsigned
		Default Unix_Timestamp()
		Not Null,
	ViewTime int
		Unsigned
		Default Null,

	Foreign Key (TypeID)
		References BikeMaster.NotificationTypes(ID),
	Foreign Key (TargetUserID)
		References BikeMaster.Users(ID),
	Foreign Key (TriggerUserID)
		References BikeMaster.Users(ID)
);
/*Table of journeys*/
Create Table BikeMaster.Journeys(
	ID int 
    	Unsigned
    	Primary Key
    	Auto_Increment 
    	Not Null,
    UserID int
    	Unsigned
    	Not Null,
	Title varchar(32)
		Not Null,
	Description varchar(512)
		Not Null,
	Length float
		Unsigned
		Not Null,
    StartTime int
    	Unsigned
    	Not Null,
    EndTime int
    	Unsigned
    	Not Null,
    CreationTime int
    	Unsigned
		Default Unix_Timestamp()
		Not Null,
	Archived tinyint
		Default 0
		Not Null,
    Foreign Key (UserID)
    	References BikeMaster.Users(ID)
);
/*Table of segments*/
Create Table BikeMaster.TrackSegments(
  	ID int
    	Unsigned
    	Primary Key 
    	Auto_Increment
    	Not Null,
    JourneyID int
    	Unsigned
    	Not Null,
    
    Foreign Key (JourneyID)
    	References BikeMaster.Journeys(ID)
);
/*Table of trackPoints*/
Create Table BikeMaster.TrackPoints(
    ID int
    	Unsigned
    	Primary Key
    	Auto_Increment 
    	Not Null,
    SegmentID int
    	Unsigned
    	Not Null,
    /*-90-90 deg. Precision 7 decimal places (1.1cm)*/
    Latitude double(9, 7)
    	Not Null,
    /*-180-180 deg. Precision 7 decimal places (1.1cm)*/
    Longitude double(10, 7)
    	Not Null,
    /*Accuracy in meters. Up to 999,999 meters Precision 3 decimal places (mm)*/
    Accuracy double(9, 3)
    	Not Null,
    /*Altitude in meters. Up to 9999 meters. Precision 3 decimal places (mm)*/
    /*Altitude double(7, 3)
    	Default Null,*/
    /*Accuracy in meters. Precision 3 decimal places (mm)*/
    /*AltitudeAccuracy double(7, 3)
    	Default Null,*/
    Timestamp bigint
    	Unsigned
    	Not Null,

    Foreign Key (SegmentID)
    	References BikeMaster.TrackSegments(ID)
);
/*Table of competitions*/
Create Table BikeMaster.Competitions(
	ID int
		Unsigned
		Primary Key
		Auto_Increment
		Not Null,
	SenderUserID int
		Unsigned
		Not Null,
	ReceiverUserID int
		Unsigned
		Not Null,
	Title varchar(32)
		Not Null,
	Description varchar(512)
		Not Null,
	Distance int
		Not Null,
	SenderDistanceDriven int
		Unsigned
		Default 0
		Not Null,
	ReceiverDistanceDriven int
		Unsigned
		Default 0
		Not Null,
	Accepted tinyint(1)
		Default 0
		Not Null,
	Finished tinyint(1)
		Default 0
		Not Null,
	RequestTime int
    	Unsigned
    	Default Unix_Timestamp()
    	Not Null,
    AcceptTime int 
    	Unsigned,
	FinishTime int
		Unsigned,
	WinnerID int
		Unsigned,

	Foreign Key (SenderUserID)
    	References BikeMaster.Users(ID),
	Foreign Key (ReceiverUserID)
    	References BikeMaster.Users(ID),
	Foreign Key (WinnerID)
		References BikeMaster.Users(ID)
);
/*Table of reasonIDs*/
Create Table BikeMaster.ReasonIDs(
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
/*NotificationTypes*/
Insert Into BikeMaster.NotificationTypes (NameID) Values
	('SendRequest'),
	('AcceptRequest'),
	('Unfriend');
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
	('Empty'),
	('OutOfRange'),
	('TooHighSpeed'),
	('TimeTravel'),
	('SetDefault'),
	('Error'),
	('NotFound'),
	('AlreadyExists'),
	('NotAllowed'),
	('NoReasonAvailable');
/*Settings*/
Insert Into BikeMaster.Settings (NameID, Value) Values
	('ProjectName', 'BikeMaster'),
	('MaximumAccuracy', '150'),
	/*Minimum altitude: https://www.universetoday.com/15027/lowest-point-on-earth/*/
	('MinimumAltitude', '-420'),
	/*Maximum altitude: https://www.britannica.com/place/Mount-Everest*/
	/*('MaximumAltitude', '8849'),
	('MaximumAltitudeAccuracy', '500'),*/
	/*Minimum time: First time application introduced (1672948580000), https://www.unixtimestamp.com/
	Actually, it's not necessary to keep minimum time to application start in case of data from other source
	*/
	('MinimumTime', '0'),
	/*Maximum speed: https://www.moultonbicycles.co.uk/heritage.html#recordsracing*/
	('MaximumSpeed', '82.54');
Create Database BikeMaster Character Set utf8 Collate utf8_general_ci;

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
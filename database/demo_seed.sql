USE ashford_university;

INSERT IGNORE INTO User VALUES
(1001,'Jordan',NULL,'Ellis','Male','2004-03-15','120 Coastal Ave','Ashford University','NY','11598','Student'),
(2001,'Elena',NULL,'Vasquez','Female','1980-06-20','10 Faculty Ln','Ashford University','NY','11598','Faculty'),
(3001,'Margaret',NULL,'Liu','Female','1975-02-10','20 Admin Rd','Ashford University','NY','11598','Admin'),
(4001,'Thomas',NULL,'Reyes','Male','1985-09-09','30 Stats Ct','Ashford University','NY','11598','StatStaff');

INSERT IGNORE INTO Login(user_ID,user_Email,user_Password,user_Type) VALUES
(1001,'jordan.ellis@ashford.edu','Password123!','Student'),
(2001,'e.vasquez@ashford.edu','Password123!','Faculty'),
(3001,'m.liu@ashford.edu','Password123!','Admin'),
(4001,'t.reyes@ashford.edu','Password123!','StatStaff');

INSERT IGNORE INTO Building VALUES ('AT','Athena Hall','Academic'),('SC','Science Center','Academic/Labs');
INSERT IGNORE INTO Room VALUES (101,'AT','101','Lecture'),(102,'AT','102','Lecture'),(200,'SC','200','Office'),(112,'SC','112','Lab');
INSERT IGNORE INTO Lecture VALUES (101,40),(102,35);
INSERT IGNORE INTO Office VALUES (200,2);
INSERT IGNORE INTO Lab VALUES (112,24);

INSERT IGNORE INTO Faculty VALUES (2001,200,'Computer Science','Professor','Full-Time',2);
INSERT IGNORE INTO Full_Time_Faculty VALUES (2001,2);

INSERT IGNORE INTO Department VALUES
(1,'Computer Science',200,'cs@ashford.edu','555-0100',2001,'Alex Morgan');
INSERT IGNORE INTO Major VALUES (1,'Computer Science (BS)',1);
INSERT IGNORE INTO Minor VALUES (1,'Computer Science',1);
INSERT IGNORE INTO Student VALUES (1001,1,4,'Undergraduate');
INSERT IGNORE INTO Undergraduate VALUES (1001,1,'Full-Time');
INSERT IGNORE INTO Full_Time_Undergraduate VALUES (1001,'Good Standing',12,16,94);

INSERT IGNORE INTO Course VALUES
(101,1,'Introduction to Programming',4,'Programming fundamentals','Undergraduate'),
(201,1,'Data Structures and Algorithms',4,'Core data structures','Undergraduate'),
(310,1,'Database Systems',3,'Relational database design','Undergraduate');

INSERT IGNORE INTO Day VALUES (1,'Monday'),(2,'Tuesday'),(3,'Wednesday'),(4,'Thursday'),(5,'Friday');
INSERT IGNORE INTO Period VALUES (1,'09:00:00','09:50:00'),(2,'11:00:00','12:15:00'),(3,'15:30:00','16:45:00');
INSERT IGNORE INTO Time_Slot VALUES (1),(2),(3);
INSERT IGNORE INTO Time_Slot_Day VALUES (1,1),(1,3),(1,5),(2,2),(2,4),(3,2),(3,4);
INSERT IGNORE INTO Time_Slot_Period VALUES (1,1),(2,2),(3,3);
INSERT IGNORE INTO Semester VALUES (1,'Fall 2026'),(2,'Spring 2027');

INSERT IGNORE INTO Course_Section VALUES
(10401,101,1,2001,1,NULL,1,7),
(10403,201,1,2001,2,NULL,1,10),
(10404,310,1,2001,3,NULL,1,9);

INSERT IGNORE INTO Advisor VALUES (2001,1001,'2026-08-20');
INSERT IGNORE INTO Enrollment(student_ID,CRN,semester_ID,Grade) VALUES (1001,10404,1,NULL);
INSERT IGNORE INTO Student_History VALUES (1001,10401,101,1,'A'),(1001,10403,201,1,'B+');
INSERT IGNORE INTO Student_Major VALUES (1001,1,'2025-01-10');
INSERT IGNORE INTO Faculty_Department VALUES (2001,1,100.00,'2020-08-15');

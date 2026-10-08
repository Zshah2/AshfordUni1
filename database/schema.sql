USE ashford_university;

CREATE TABLE User (
    user_ID INT PRIMARY KEY,
    first_Name VARCHAR(50) NOT NULL,
    middle_Name VARCHAR(50) DEFAULT NULL,
    last_Name VARCHAR(50) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    DOB DATE NOT NULL,
    street VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    state VARCHAR(50) NOT NULL,
    zip_Code VARCHAR(10) NOT NULL,
    phone_no VARCHAR(20) NOT NULL,
    user_Type ENUM('Student', 'Faculty', 'Admin', 'StatStaff') NOT NULL
);



CREATE TABLE Admin (
    admin_ID INT PRIMARY KEY,
    security_Level ENUM('READ_ONLY', 'LIMITED', 'READ_WRITE') NOT NULL,
    priority_Level INT NOT NULL,
    FOREIGN KEY (admin_ID) REFERENCES User(user_ID)
);

CREATE TABLE Stat_Dept_Member (
    stat_ID INT PRIMARY KEY,
    employment_Status ENUM('Full-Time') NOT NULL DEFAULT 'Full-Time',
    FOREIGN KEY (stat_ID) REFERENCES User(user_ID)
);

CREATE TABLE Password_Reset (
    reset_ID INT AUTO_INCREMENT PRIMARY KEY,
    user_ID INT NOT NULL,
    reset_Code VARCHAR(10) NOT NULL,
    expires_At DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_ID) REFERENCES User(user_ID)
);

CREATE TABLE Login (
    user_ID INT PRIMARY KEY,
    user_Email VARCHAR(255) NOT NULL UNIQUE,
    user_Password VARCHAR(255) NOT NULL,
    no_Of_Tries INT NOT NULL DEFAULT 0,
    lock_var BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (user_ID) REFERENCES User(user_ID)
);

CREATE TABLE Building (
    Bldg_ID VARCHAR(8) PRIMARY KEY,
    Bldg_Name VARCHAR(255) NOT NULL,
    building_Usage VARCHAR(255) NOT NULL
);

CREATE TABLE Room (
    room_ID INT PRIMARY KEY,
    Bldg_ID VARCHAR(8) NOT NULL,
    room_Number VARCHAR(20) NOT NULL,
    room_Type ENUM('Lecture', 'Office', 'Lab') NOT NULL,
    FOREIGN KEY (Bldg_ID) REFERENCES Building(Bldg_ID)
);

CREATE TABLE Lecture (
    lecture_ID INT PRIMARY KEY,
    avail_Seats INT NOT NULL,
    FOREIGN KEY (lecture_ID) REFERENCES Room(room_ID)
);

CREATE TABLE Office (
    office_ID INT PRIMARY KEY,
    No_Of_Desks INT NOT NULL,
    FOREIGN KEY (office_ID) REFERENCES Room(room_ID)
);

CREATE TABLE Lab (
    lab_ID INT PRIMARY KEY,
    no_Of_Workstations INT NOT NULL,
    FOREIGN KEY (lab_ID) REFERENCES Room(room_ID)
);

CREATE TABLE Faculty (
    faculty_ID INT PRIMARY KEY,
    office_ID INT NOT NULL,
    Specialty VARCHAR(100) NOT NULL,
    faculty_Rank VARCHAR(50) NOT NULL,
    faculty_Type ENUM('Full-Time', 'Part-Time') NOT NULL,
    FOREIGN KEY (faculty_ID) REFERENCES User(user_ID),
    FOREIGN KEY (office_ID) REFERENCES Office(office_ID)
);

CREATE TABLE Full_Time_Faculty (
    faculty_ID INT PRIMARY KEY,
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID)
);

CREATE TABLE Part_Time_Faculty (
    faculty_ID INT PRIMARY KEY,
    DOA DATE NOT NULL,
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID)
);

CREATE TABLE Department (
    dept_ID INT PRIMARY KEY,
    dept_Name VARCHAR(100) NOT NULL,
    office_ID INT NOT NULL,
    Email VARCHAR(255) NOT NULL UNIQUE,
    Phone_No VARCHAR(20) NOT NULL,
    chair_ID INT NOT NULL,
    dept_Manager VARCHAR(100) NOT NULL,
    FOREIGN KEY (office_ID) REFERENCES Office(office_ID),
    FOREIGN KEY (chair_ID) REFERENCES Faculty(faculty_ID)
);

CREATE TABLE Major (
    major_ID INT PRIMARY KEY,
    major_Name VARCHAR(100) NOT NULL,
    dept_ID INT NOT NULL,
    credits_required INT NOT NULL,
    FOREIGN KEY (dept_ID) REFERENCES Department(dept_ID)
);

CREATE TABLE Major_Requirement (
    major_ID INT NOT NULL,
    course_ID INT NOT NULL,
    min_Grade VARCHAR(5) NOT NULL,
    PRIMARY KEY (major_ID, course_ID),
    FOREIGN KEY (major_ID) REFERENCES Major(major_ID),
    FOREIGN KEY (course_ID) REFERENCES Course(course_ID)
);

CREATE TABLE Minor (
    minor_ID INT PRIMARY KEY,
    minor_Name VARCHAR(100) NOT NULL,
    dept_ID INT NOT NULL,
    credits_required INT NOT NULL,
    FOREIGN KEY (dept_ID) REFERENCES Department(dept_ID)
);

CREATE TABLE Minor_Requirement (
    minor_ID INT NOT NULL,
    course_ID INT NOT NULL,
    min_Grade VARCHAR(5) NOT NULL,
    PRIMARY KEY (minor_ID, course_ID),
    FOREIGN KEY (minor_ID) REFERENCES Minor(minor_ID),
    FOREIGN KEY (course_ID) REFERENCES Course(course_ID)
);

CREATE TABLE Semester (
    semester_ID INT PRIMARY KEY,
    semester_Name ENUM('Fall', 'Spring') NOT NULL,
    semester_Year INT NOT NULL,

    start_Date DATE NOT NULL,
    end_Date DATE NOT NULL,

    registration_Start DATETIME NOT NULL,
    registration_End DATETIME NOT NULL,
    drop_Deadline DATETIME NOT NULL,

    midterm_Grade_Start DATETIME,
    midterm_Grade_End DATETIME,

    final_Grade_Start DATETIME,
    final_Grade_End DATETIME
);

CREATE TABLE Academic_Calendar (
    calendar_ID INT AUTO_INCREMENT PRIMARY KEY,
    semester_ID INT,
    event_Name VARCHAR(150) NOT NULL,
    start_Date DATE NOT NULL,
    end_Date DATE,
    FOREIGN KEY (semester_ID) REFERENCES Semester(semester_ID)
);

CREATE TABLE Activity_Log (
    log_ID BIGINT AUTO_INCREMENT PRIMARY KEY,
    admin_ID INT,
    action_Type VARCHAR(50) NOT NULL,
    affected_User_ID INT,
    table_Name VARCHAR(50),
    old_Value TEXT,
    new_Value TEXT,
    action_Time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (admin_ID) REFERENCES Admin(admin_ID)
);

CREATE TABLE Notification (
    notification_ID BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_ID INT NOT NULL,
    message VARCHAR(500) NOT NULL,
    created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_Status BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (user_ID) REFERENCES User(user_ID)
);

CREATE TABLE Student (
    student_ID INT PRIMARY KEY,
    student_Year INT NOT NULL,
    student_Type ENUM('Undergraduate', 'Graduate') NOT NULL,
    FOREIGN KEY (student_ID) REFERENCES User(user_ID),
);

CREATE TABLE Undergraduate (
    student_ID INT PRIMARY KEY,
    dept_ID INT NOT NULL,
    undergraduate_student_Type ENUM('Full-Time', 'Part-Time') NOT NULL,
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (dept_ID) REFERENCES Department(dept_ID)
);

CREATE TABLE Full_Time_Undergraduate (
    student_ID INT PRIMARY KEY,
    Status VARCHAR(50) NOT NULL,
    min_Credits INT NOT NULL,
    max_Credits INT NOT NULL,
    credits_Earned INT NOT NULL DEFAULT 0,
    FOREIGN KEY (student_ID) REFERENCES Undergraduate(student_ID)
);

CREATE TABLE Part_Time_Undergraduate (
    student_ID INT PRIMARY KEY,
    Status VARCHAR(50) NOT NULL,
    min_Credits INT NOT NULL,
    max_Credits INT NOT NULL,
    credits_Earned INT NOT NULL DEFAULT 0,
    FOREIGN KEY (student_ID) REFERENCES Undergraduate(student_ID)
);

CREATE TABLE Graduate_Student (
    student_ID INT PRIMARY KEY,
    dept_ID INT NOT NULL,
    Program VARCHAR(100) NOT NULL,
    graduate_Student_Type ENUM('Full-Time', 'Part-Time') NOT NULL,
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (dept_ID) REFERENCES Department(dept_ID)
);

CREATE TABLE Full_Time_Graduate (
    student_ID INT PRIMARY KEY,
    year INT NOT NULL,
    credits_Earned INT NOT NULL DEFAULT 0,
    Thesis VARCHAR(255),
    FOREIGN KEY (student_ID) REFERENCES Graduate_Student(student_ID)
);

CREATE TABLE Part_Time_Graduate (
    student_ID INT PRIMARY KEY,
    FOREIGN KEY (student_ID) REFERENCES Graduate_Student(student_ID)
);

CREATE TABLE Hold (
    hold_ID INT PRIMARY KEY,
    hold_Type VARCHAR(100) NOT NULL
);

CREATE TABLE Course (
    course_ID VARCHAR(10) PRIMARY KEY,
    dept_ID INT NOT NULL,
    course_Name VARCHAR(255) NOT NULL,
    course_Credits INT NOT NULL,
    course_DSC VARCHAR(500) NOT NULL,
    course_Type ENUM('Undergraduate', 'Graduate') NOT NULL,
    FOREIGN KEY (dept_ID) REFERENCES Department(dept_ID)
);

CREATE TABLE Day (
    day_ID INT PRIMARY KEY,
    day_Of_Week ENUM(
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday'
    ) NOT NULL
);

CREATE TABLE Period (
    period_ID INT PRIMARY KEY,
    start_Time TIME NOT NULL,
    end_Time TIME NOT NULL
);

CREATE TABLE Time_Slot (
    time_Slot_ID INT PRIMARY KEY
);

CREATE TABLE Time_Slot_Meeting (
    time_slot_ID INT NOT NULL,
    day_ID INT NOT NULL,
    period_ID INT NOT NULL,
    PRIMARY KEY  (
    time_slot_ID,
    day_ID,
    period_ID,
    ),
    FOREIGN KEY (time_slot_ID) REFERENCES Time_Slot(time_slot_ID),
    FOREIGN KEY (day_ID) REFERENCES Day(day_ID),
    FOREIGN KEY (period_ID) REFERENCES Period(period_ID)
    );

CREATE TABLE Semester (
    semester_ID INT PRIMARY KEY,
    semester_Name VARCHAR(50) NOT NULL
);

CREATE TABLE Course_Section (
    CRN INT PRIMARY KEY,
    course_ID INT NOT NULL,
    section_No INT NOT NULL,
    faculty_ID INT NOT NULL,
    time_Slot_ID INT NOT NULL,
    room_ID INT NOT NULL,
    semester_ID INT NOT NULL,
    available_Seats INT NOT NULL,
    FOREIGN KEY (course_ID) REFERENCES Course(course_ID),
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID),
    FOREIGN KEY (room_ID) REFERENCES Room(room_ID),
    FOREIGN KEY (time_Slot_ID) REFERENCES Time_Slot(time_Slot_ID),
    FOREIGN KEY (semester_ID) REFERENCES Semester(semester_ID)
);

CREATE TABLE Advisor (
    faculty_ID INT NOT NULL,
    student_ID INT NOT NULL,
    date_Of_Appnt DATE NOT NULL,
    PRIMARY KEY (faculty_ID, student_ID),
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID),
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID)
);

CREATE TABLE Student_Hold (
    hold_ID INT NOT NULL,
    student_ID INT NOT NULL,
    hold_Date DATE NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    released_date DATE,
    PRIMARY KEY (student_ID, hold_ID),
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (hold_ID) REFERENCES Hold(hold_ID)
);

CREATE TABLE Course_Prerequisite (
    course_ID INT NOT NULL,
    prerequisite_course_ID INT NOT NULL,
    min_Grade_Req VARCHAR(5) NOT NULL,
    PRIMARY KEY (course_ID, prerequisite_course_ID),
    FOREIGN KEY (course_ID) REFERENCES Course(course_ID),
    FOREIGN KEY (prerequisite_course_ID) REFERENCES Course(course_ID)
);

CREATE TABLE Enrollment (
    student_ID INT NOT NULL,
    CRN INT NOT NULL,
    enrollment_Status ENUM(
        'Registered',
        'Dropped',
        'Completed'
    ) NOT NULL DEFAULT 'Registered',
    registration_Date DATETIME NOT NULL,
    drop_Date DATETIME,
    Grade VARCHAR(5),
    PRIMARY KEY (student_ID, CRN),
    FOREIGN KEY (student_ID)
    REFERENCES Student(student_ID),
    FOREIGN KEY (CRN)
    REFERENCES Course_Section(CRN)
);

CREATE TABLE Attendance (
    student_ID INT NOT NULL,
    CRN INT NOT NULL,
    attendance_Date DATE NOT NULL,
    Present_Absent ENUM('Present', 'Absent') NOT NULL,
    PRIMARY KEY (student_ID, CRN, attendance_Date),
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (CRN) REFERENCES Course_Section(CRN),
);

CREATE TABLE Faculty_Department (
    faculty_ID INT NOT NULL,
    dept_ID INT NOT NULL,
    percent_Time DECIMAL(5,2) NOT NULL,
    date_Of_Appointment DATE NOT NULL,
    PRIMARY KEY (faculty_ID, dept_ID),
    FOREIGN KEY (faculty_ID) REFERENCES Faculty(faculty_ID),
    FOREIGN KEY (dept_ID) REFERENCES Department(dept_ID)
);

CREATE TABLE Student_Major (
    student_ID INT NOT NULL,
    major_ID INT NOT NULL,
    date_Of_Choice DATE NOT NULL,
    PRIMARY KEY (student_ID, major_ID),
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (major_ID) REFERENCES Major(major_ID)
);

CREATE TABLE Student_Minor (
    student_ID INT NOT NULL,
    minor_ID INT NOT NULL,
    date_Of_Choice DATE NOT NULL,
    PRIMARY KEY (student_ID, minor_ID),
    FOREIGN KEY (student_ID) REFERENCES Student(student_ID),
    FOREIGN KEY (minor_ID) REFERENCES Minor(minor_ID)
);


CREATE TABLE IF NOT EXISTS Exam (
    idExam INT PRIMARY KEY,
    name VARCHAR(50),
    status VARCHAR(50),
    code VARCHAR(50),
    scale VARCHAR(50),
    hasMalus BOOL,
    time VARCHAR(50),
    idQuizz INT REFERENCES Quizz(idQuizz),
    idGroup INT REFERENCES Group(idGroup)
);

CREATE TABLE IF NOT EXISTS User (
    id_s11 INT PRIMARY KEY,
    pwd VARCHAR(50),
    role VARCHAR(50),
    lastname VARCHAR(50),
    firstname VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS Group (
    idGroup INT PRIMARY KEY,
    name VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS Quizz (
    idQuizz INT PRIMARY KEY,
    name VARCHAR(50),
    isEnable BOOL,
    id_s11 INT REFERENCES User(id_s11)
);

CREATE TABLE IF NOT EXISTS Question (
    idQuestion INT PRIMARY KEY,
    name TEXT,
    isEnable BOOL,
    idQuizz INT REFERENCES Quizz(idQuizz)
);

CREATE TABLE IF NOT EXISTS Answer (
    idAnswer INT PRIMARY KEY,
    name TEXT,
    isCorrect BOOL,
    idQuestion INT REFERENCES Question(idQuestion)
);

CREATE TABLE IF NOT EXISTS BelongGroup (
    id_s11 INT REFERENCES User(id_s11),
    idGroup INT REFERENCES Group(idGroup),
    PRIMARY KEY(id_s11,idGroup)
);

CREATE TABLE IF NOT EXISTS TakeExam (
    id_s11 INT REFERENCES User(id_s11),
    idExam INT REFERENCES Exam(idExam),
    date_exam DATETIME,
    answer TEXT,
    grade FLOAT,
    PRIMARY KEY(id_s11,idExam)
);

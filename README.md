# CodeChallenges
_The code challenges web platform for Michael Reeve's Discord._

## Database
The DBMS is SQLite (yeah, I know...). It should be portable to any relational DBMS such as MariaDB.
The schema is as follows:

### Table USERS
+ ID (integer, primary key, not null). It's the ID of the Discord user.
+ USERNAME (text). Gets updated every time the user logs in.
+ MODE (integer, default: 0). Represents the permissions of the user. There are three levels: 0 (regular user, can solve challenges), 1 (staff, can create and delete challenges as well as review solutions), 2 (admin, can view the logs and manage the users).
+ POINTS (integer, default: 0). Well, the points the user has been awarded.

### Table CHALLENGES
+ ID (integer, primary key, not null, autoincrement).
+ NAME (text).
+ DESC (text). Description.
+ POINTS (integer).
+ CREATOR (integer). The ID of the user who created it.
+ DATE (integer). UTC POSIX timestamp.

### Table SUBMITS
It should be called "SUBMISSIONS". I made a typo and I'm not even thinking about fixing it.

+ ID (integer, primary key, not null, autoincrement).
+ USER_ID (integer).
+ CHALLENGE_ID (integer).
+ URL (text). The URL which contains the code of the submission.
+ DATE (integer).
+ STATE (integer). Two possible values: 0 (pending) or 1 (accepted).

### Table NEWS
+ ID (integer, primary key, not null, autoincrement).
+ USER_ID (integer).
+ CHALLENGE_ID (integer).
+ TYPE (integer). Two values: 0 (a submission has been rejected), 1 (accepted).
+ DATE (integer).

### Table LOG
+ ID (integer, primary key, not null, autoincrement).
+ DATE (integer).
+ CONTENT (text). The text that will be shown to the administrators.

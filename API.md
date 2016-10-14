# Attendance Taking: API Reference
Attendance Taking server provides the following list of API
## User API
- [Log in for student](#post-studentlogin)
- [Log in for lecturer](#post-lecturerlogin)
- [Sign up for student](#post-studentsignup)
- [Sign up for lecturer](#post-lecturersignup)
- [Register new device for student](#post-studentregister-device)
- [Get person id](#get-userminefieldsperson_id)
- [Get face id](#get-userminefieldsface_id)
- [Set person id](#post-usermine)
- [Set face id](#post-usermine_1)
- [Log out](#post-userlogout)
- [Change password](#post-userchange-password)
- [Reset password](#post-userreset-password)
- [Allow training face for lecturer](#post-userallow-train-face)
- [Disallow training face for lecturer](#post-userdisallow-train-face)
- [Check training face for student](#get-usercheck-train-face)
- [Train face for student](#post-usertrain-face)
- [Get student profile](#get-studentprofileexpanduser)
- [Get lecturer profile](#get-lecturerprofileexpanduser)

## Attendance API
- [Get timetable of one day for student/lecturer](#get-attendancedayrecorded_date2016-10-12expandlesson)
- [Get timetable of one week for student/lecturer](#get-attendanceweekweeknumber1expandlesson)
- [Get timetable of current semester for lecturer](#get-attendancesemesterfromdate2016-10-05class_sectionll12expandlessonstudent)
- [Get attendance history for student](#get-attendancehistoryfromdate2016-10-5todate2016-10-15class_sectiont1m2expandlesson)
- [Take attendance by face for student](#post-attendanceface)
- [Update attendance of student for lecturer](#post-attendanceattendance-id)

## API Details
###POST ```student/login```
```
=> Login to student app
```
####Header: None
####Request:
```
{
  "username":"canhnht",
  "password":"123456",
  "device_hash":"f8:32:e4:5f:6f:35"
}
```
####Response:
- Success: 200
```
{
    "id": "2",
    "name": "MICHAEL YOO",
    "acad": "AE",
    "token": "vQ-vFSyjB0dLPtusRHshhPzk8WPRYpv-"
}

```
- Error: 400
```
{
  code: (
    0: CODE_INCORRECT_USERNAME,
    1: CODE_INCORRECT_PASSWORD,
    2: CODE_INCORRECT_DEVICE,
    3: CODE_UNVERIFIED_EMAIL,
    4: CODE_UNVERIFIED_DEVICE,
    5: CODE_UNVERIFIED_EMAIL_DEVICE,
    6: CODE_INVALID_ACCOUNT
  )
}
```

*******************

###POST ```lecturer/login```
```
=> Login to lecturer app
```
####Header: None
####Request:
```
{
  "username":"zhangqinjie",
  "password":"123456"
}
```
####Response:
- Success: 200
```
{
    "name": "Zhang Qinjie",
    "acad": "ECE",
    "email": "zhangqinjie@mail.com",
    "token": "gUzMJjd4fDxQ4s0IYOYTWws9i6EppwH7"
}

```
- Error: 400
```
{
  code: (
    0: CODE_INCORRECT_USERNAME,
    1: CODE_INCORRECT_PASSWORD,
    2: CODE_INCORRECT_DEVICE,
    3: CODE_UNVERIFIED_EMAIL,
    4: CODE_UNVERIFIED_DEVICE,
    5: CODE_UNVERIFIED_EMAIL_DEVICE,
    6: CODE_INVALID_ACCOUNT
  )
}
```

***

###POST ```student/signup```
```
=> Sign up new user for a student. Activation email will be sent to email of user. Device is activated in next day.
```
####Header: None
####Request:
```
{
  "username":"student",
  "password":"123456",
  "student_id":"55555555B",
  "device_hash":"11:11:11:11:11:11",
  "email":"student@mail.com"
}
```
####Response:
```
{
    "token": "uEm4DM5jBjksjKuyfHwLmVdujUK4KQ8g"
}

```

***

###POST ```lecturer/signup```
```
=> Sign up new user for a lecturer. An activation email will be sent to email of user.
```
####Header: None
####Request:
```
{
  "username":"lecturer",
  "password":"123456",
  "email":"lecturer@mail.com"
}
```
####Response:
```
{
    "token": "W-FWF1e5_cXljYw9muk0NFi8oh3TYy33"
}
```

***

###POST ```student/register-device```
```
=> Register new device for a student. New device will be activated in next day.
```
####Header: None
####Request:
```
{
  "username":"canhnht",
  "password":"123456",
  "device_hash":"11:11:11:11:11:11"
}
```
####Response:
- Success: 200
```
"register device successfully"
```
- Error: 400
```
{
  code: (
    0: CODE_INCORRECT_USERNAME,
    1: CODE_INCORRECT_PASSWORD,
    6: CODE_INVALID_ACCOUNT,
    7: CODE_DUPLICATE_DEVICE
  )
}
```

***

###GET ```user/mine?fields=person_id```
```
=> Get person Id for face++
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request: None
####Response:
```
{
    "person_id": "2bab14e81845032bd184f2f08e181300"
}

```

***

###GET ```user/mine?fields=face_id```
```
=> Get face Id for face++
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request: None
####Response:
```
{
    "face_id": [
        "0d3df55d5f5bbfab9d80b7457ecc461d"
    ]
}

```

***

###POST ```user/mine```
```
=> Set person Id for face++
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request:
```
{
  "person_id":"2bab14e81845032bd184f2f08e181300"
}
```
####Response:
```
{
    "person_id": "2bab14e81845032bd184f2f08e181300",
    "face_id": [
        "0d3df55d5f5bbfab9d80b7457ecc461d"
    ],
    "username": "canhnht",
    "email": "canh@mail.com"
}

```

***

###POST ```user/mine```
```
=> Set face Id for face++. 
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request:
```
{
  "face_id":["face-id-1","face-id-2"]
}
```
####Response:
```
{
    "person_id": "2bab14e81845032bd184f2f08e181300",
    "face_id": [
        "face-id-1",
        "face-id-2"
    ],
    "username": "canhnht",
    "email": "canh@mail.com"
}

```

*****************

###POST ```user/logout```
```
=> Log out app
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request: None
####Response:
```
"logout successfully"
```

***

###POST ```user/change-password```
```
=> Change password
```
####Header: None
####Request:
```
{
  "oldPassword":"123456",
  "newPassword":"654321"
}
```
####Response:
- Success: 200
```
"change password successfully"
```
- Error: 400
```
{
  code: (
    1: CODE_INCORRECT_PASSWORD,
    8: CODE_INVALID_PASSWORD
  )
}
```

***

###POST ```user/reset-password```
```
=> Request a new password. An email will be sent to user containing a link for resetting password.
```
####Header: None
####Request:
```
{
  "email":"canh@mail.com"
}
```
####Response:
- Success: 200
```
"reset password successfully"
```

***

###POST ```user/allow-train-face```
```
=> When a lecturer allows one student to train face
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request:
```
{
  "studentId":"2"
}
```
####Response:
```
"allow training face successfully"
```

***

###POST ```user/disallow-train-face```
```
=> When a lecturer disallows one student to train face
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request:
```
{
  "studentId":"2"
}
```
####Response:
```
"disable training face successfully"
```

***

###GET ```user/check-train-face```
```
=> Check if a student can train face
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request: None
####Response:
```
{
  "result": (true, false)
}
```

***

###POST ```user/train-face```
```
=> Train one face id for person id of a student. Clear all face id (optional).
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request:
```
{
  "faceId":"0d3df55d5f5bbfab9d80b7457ecc461d",
  "clearFace":false
}
```
####Response:
```
{
    "id": 57,
    "person_id": "2bab14e81845032bd184f2f08e181300",
    "face_id": [
        "face-id-1",
        "face-id-2",
        "0d3df55d5f5bbfab9d80b7457ecc461d"
    ],
    "username": "canhnht",
    "device_hash": "f8:32:e4:5f:6f:35",
    "email": "canh@mail.com",
    "profileImg": null,
    "status": 10,
    "role": 20,
    "name": "student"
}
```

****************

###GET ```student/profile?expand=user```
```
=> Get profile of a student
```
####Header:
```
Authorization: 'Bearer <token>'
```

####Request: None
####Response:
```
{
    "id": "2",
    "name": "MICHAEL YOO",
    "gender": null,
    "acad": "AE",
    "uuid": null,
    "user_id": 57,
    "user": {
        "id": 57,
        "person_id": "2bab14e81845032bd184f2f08e181300",
        "face_id": [
            "0d3df55d5f5bbfab9d80b7457ecc461d"
        ],
        "username": "canhnht",
        "device_hash": "f8:32:e4:5f:6f:35",
        "email": "canh@mail.com",
        "profileImg": null,
        "status": 10,
        "role": 20,
        "name": "student"
    }
}
```

***

###GET ```lecturer/profile?expand=user```
```
=> Get profile of a lecturer
```
####Header:
```
Authorization: 'Bearer <token>'
```

####Request: None
####Response:
```
{
    "id": "1",
    "name": "Zhang Qinjie",
    "acad": "ECE",
    "email": "zhangqinjie@mail.com",
    "user_id": 60,
    "user": {
        "id": 60,
        "person_id": "",
        "face_id": [],
        "username": "zhangqinjie",
        "device_hash": null,
        "email": "zhangqinjie@mail.com",
        "profileImg": null,
        "status": 10,
        "role": 30,
        "name": "lecturer"
    }
}

```

***

###GET ```attendance/day?recorded_date=2016-10-12&expand=lesson,venue```
```
=> Get all lessons of a student/lecturer for one day, sorted by start time.
If there's no recorded_date, lessons of today will be returned.
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request: None
####Response:
```
[
    {
        "id": 1210,
        "student_id": "2",
        "lesson_id": 17,
        "lecturer_id": "9580",
        "recorded_date": "2016-10-12",
        "recorded_time": null,
        "is_absent": null,
        "is_late": null,
        "late_min": 0,
        "created_at": "0000-00-00 00:00:00",
        "updated_at": null,
        "lesson": {
            "id": 17,
            "semester": "2",
            "module_id": "007777",
            "subject_area": "AE",
            "catalog_number": "2FAT",
            "class_section": "LM12",
            "component": "LEC",
            "facility": "08-04-0001",
            "venue_id": 3,
            "weekday": "WED",
            "start_time": "09:00",
            "end_time": "10:00",
            "meeting_pattern": "",
            "created_at": "0000-00-00 00:00:00",
            "updated_at": "2016-04-26 11:09:19"
        }
    }
]
```

********************

###GET ```attendance/week?weekNumber=1&expand=lesson,venue```
```
=> Get all lessons of a student/lecturer for one week, sorted by day and start time.
If there's no weekNumber, lessons of current week will be returned.
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request: None
####Response:
```
[
    {
        "id": 804,
        "student_id": "2",
        "lesson_id": 28,
        "lecturer_id": "9580",
        "recorded_date": "2016-10-10",
        "recorded_time": null,
        "is_absent": null,
        "is_late": null,
        "late_min": 0,
        "created_at": "0000-00-00 00:00:00",
        "updated_at": null,
        "lesson": {
            "id": 28,
            "semester": "2",
            "module_id": "009521",
            "subject_area": "IS PDA",
            "catalog_number": "7COMISS",
            "class_section": "T03",
            "component": "TUT",
            "facility": "05-04-0009",
            "venue_id": 2,
            "weekday": "MON",
            "start_time": "08:00",
            "end_time": "12:00",
            "meeting_pattern": "",
            "created_at": "0000-00-00 00:00:00",
            "updated_at": "2016-04-26 11:09:19"
        }
    }
]
```

********************

###GET ```attendance/semester?fromDate=2016-10-05&class_section=LL12&expand=lesson,student```
```
=> Get all lessons of a lecturer from <fromDate> backwards, sorted by day and start time.
If there's no fromDate, lessons from today will be returned.
If there's no class_section, lessons from all class_section will be returned.
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request: None
####Response:
```
[
    {
        "id": 799,
        "student_id": "1",
        "lesson_id": 13,
        "lecturer_id": "1",
        "recorded_date": "2016-10-10",
        "recorded_time": null,
        "is_absent": null,
        "is_late": null,
        "late_min": 0,
        "created_at": "0000-00-00 00:00:00",
        "updated_at": null,
        "lesson": {
            "id": 13,
            "semester": "2",
            "module_id": "005696",
            "subject_area": "IS MATH",
            "catalog_number": "1EM3A",
            "class_section": "LL12",
            "component": "LEC",
            "facility": "04-02-0002",
            "venue_id": 1,
            "weekday": "MON",
            "start_time": "13:00",
            "end_time": "15:00",
            "meeting_pattern": "",
            "created_at": "0000-00-00 00:00:00",
            "updated_at": "2016-04-26 11:09:19"
        },
        "student": {
            "id": "1",
            "name": "ADRIAN YOO",
            "gender": null,
            "acad": "AE",
            "uuid": null,
            "user_id": 53
        }
    }
]
```

***************

###GET ```attendance/history?fromDate=2016-10-5&toDate=2016-10-15&class_section=T1M2&expand=lesson```
```
=> Get attendance history in current semester for student.
If there's no fromDate, attendance from beginning of semester will be returned.
If there's no toDate, attendance until end of semester will be returned.
If there's no class_section, attendance of all class_section will be returned.
```
####Header:
```
Authorization: 'Bearer <token>'
```
###Request: None
###Response:
```
[
    {
        "id": 51,
        "student_id": "2",
        "lesson_id": 28,
        "lecturer_id": "9580",
        "recorded_date": "2016-10-03",
        "recorded_time": null,
        "is_absent": null,
        "is_late": null,
        "late_min": 0,
        "created_at": "0000-00-00 00:00:00",
        "updated_at": null,
        "lesson": {
            "id": 28,
            "semester": "2",
            "module_id": "009521",
            "subject_area": "IS PDA",
            "catalog_number": "7COMISS",
            "class_section": "T03",
            "component": "TUT",
            "facility": "05-04-0009",
            "venue_id": 2,
            "weekday": "MON",
            "start_time": "08:00",
            "end_time": "12:00",
            "meeting_pattern": "",
            "created_at": "0000-00-00 00:00:00",
            "updated_at": "2016-04-26 11:09:19"
        }
    }
]
```

**************

###POST ```attendance/face```
```
=> Take attendance by facial recognition. One student can take attendance only once for 1 subject.
Train face if taking attendance successfully.
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request:
```
{
  "id":1210,
  "face_id":"0d3df55d5f5bbfab9d80b7457ecc461d"
}
```
####Response:
```
{
    "id": 1210,
    "student_id": "2",
    "lesson_id": 17,
    "lecturer_id": "9580",
    "recorded_date": "2016-10-12",
    "recorded_time": "10:42",
    "is_absent": 0,
    "is_late": 1,
    "late_min": 102,
    "created_at": "0000-00-00 00:00:00",
    "updated_at": null
}
```

**************

###POST ```attendance/<attendance-id>```
```
=> Update attendance of <attendance-id> for lecturer.
If student is absent, request will be:
{
  "is_absent":1,
  "is_late":0
}
If student is late, request will be:
{
  "is_absent":0,
  "is_late":1,
  "recorded_time":"10:10"
}
If student is present, request will be:
{
  "is_absent":0,
  "is_late":0
}
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request:
```
{
  "is_absent":0,
  "is_late":1,
  "recorded_time":"10:10"
}
```
####Response:
```
{
    "id": 1212,
    "student_id": "4",
    "lesson_id": 17,
    "lecturer_id": "1",
    "recorded_date": "2016-10-12",
    "recorded_time": "10:10",
    "is_absent": "0",
    "is_late": "1",
    "late_min": 70,
    "created_at": "0000-00-00 00:00:00",
    "updated_at": "2016-10-12 10:42:16"
}
```
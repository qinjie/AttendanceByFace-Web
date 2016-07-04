#Attendance System
##API
###GET ```api/check-student```
```
=> Check if current user is student
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request: None
####Response:
```
{
    result: 'You are student'
}
```

***

###GET ```api/check-teacher```
```
=> Check if current user is teacher
```
####Header:
```
Authorization: 'Bearer <token>'
```

####Request: None
####Response:
```
{
    result: 'You are teacher'
}
```

***

###POST ```user/login```
```
=> Login to app
```
####Header: None
####Request:
```
{
    username: '1234',
    password: '123456',
    device_hash
}
```
####Response:
- Success: 200
```
{
    token: '3kj2rh3k2rhk2j3hkj42hk43h2kh4j32'
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

###POST ```user/signup```
```
=> Sign up new user
```
####Header: None
####Request:
```
{
    username: '1234',
    password: '123456',
    email: '123@mail.com',
    role: (10: role user, 20: role student, 30: role teacher),
    device_hash
}
```
####Response:
```
{
    token: '3kj2rh3k2rhk2j3hkj42hk43h2kh4j32'
}
```

***

###GET ```user/logout```
```
=> Log out app
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request:
```
{
    username: '1234',
    password: '123456',
    email: '123@mail.com',
    role: (10 for user, 20 for student, 30 for teacher)
}
```
####Response:
```
logout successful
```

***

###POST ```user/register-device```
```
=> Register new device for a user
```
####Header: None
####Request:
```
{
    username: '1234',
    password: '123456',
    device_hash
}
```
####Response:
- Success: 200
```
{}
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
###GET ```user/person-id```
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
    user_id: '1',
    person_id: '23dkj3hd2jg3k2hj2'
}
```

***

###GET ```user/face-id```
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
    user_id: '1',
    face_id: [
        'ef2323f2df23d', 
        '23d23d23e23'
    ]
}
```

***

###POST ```user/set-person-id```
```
=> Set person Id for face++
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request:
```
person_id
```
####Response:
```
{
    result: 1
}
```

***

###POST ```user/set-face-id```
```
=> Set face Id for face++
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request:
```
face_id
```
####Response:
```
{
    result: 1
}
```

***

###GET ```timetable/today```
```
=> Get all lessons for today, sorted by start time
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
        lesson_id,
        subject_area,
        class_section,
        component,
        start_time,
        end_time,
        weekday,
        venue_id,
        location,
        name,
        timetable_id,
        uuid,
        major,
        minor,
        status: (0: not yet, 1: present, 2: absent, 3: late),
        recorded_at
    }
]
```

***

###GET ```timetable/week?week=1```
```
=> Get all lessons for one week. For each week day, sort lessons by start time
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request: None
####Response:
```
{
  "MON": [
    {
      "lesson_id": "1",
      "subject_area": "ELECTRO",
      "class_section": "L2L",
      "component": "LEC",
      "weekday": 0,
      "start_time": "10:00",
      "end_time": "12:00",
      "location": "Location 1",
      "meeting_pattern": ""
    }
  ],
  "TUES": [
    {
      "lesson_id": "6",
      "subject_area": "ELECTRO",
      "class_section": "P2L1",
      "component": "PRA",
      "weekday": 1,
      "start_time": "13:00",
      "end_time": "15:00",
      "location": "Location 1",
      "meeting_pattern": ""
    },
    {
      "lesson_id": "4",
      "subject_area": "ELECTRO",
      "class_section": "T2L1",
      "component": "TUT",
      "weekday": 1,
      "start_time": "15:00",
      "end_time": "16:00",
      "location": "Location 1",
      "meeting_pattern": ""
    }
  ]
}
```

***

###GET ```timetable/total-week```
```
=> Get all lessons for 5 weeks. For each week day, sort lessons by start time
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
    "MON": [
      {
        "lesson_id": "1",
        "subject_area": "ELECTRO",
        "class_section": "L2L",
        "component": "LEC",
        "weekday": 0,
        "start_time": "10:00",
        "end_time": "12:00",
        "location": "Location 1",
        "meeting_pattern": ""
      }
    ],
    "TUES": [
      {
        "lesson_id": "6",
        "subject_area": "ELECTRO",
        "class_section": "P2L1",
        "component": "PRA",
        "weekday": 1,
        "start_time": "13:00",
        "end_time": "15:00",
        "location": "Location 1",
        "meeting_pattern": ""
      },
      {
        "lesson_id": "4",
        "subject_area": "ELECTRO",
        "class_section": "T2L1",
        "component": "TUT",
        "weekday": 1,
        "start_time": "15:00",
        "end_time": "16:00",
        "location": "Location 1",
        "meeting_pattern": ""
      }
    ]
  }
]
```

***

###POST ```timetable/check-attendance```
```
=> Return whether or not user can take attendance now. 
Check if request time is from (start_time - 15 minutes) 
to (start_time + 15 minutes)
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request:
```
{
  timetable_id
}
```
####Response:
```
{
  result: ('true', 'false'),
  currentTime: '10:00'
}
```

***

###POST ```timetable/take-attendance```
```
=> Take attendance. One student can take attendance only once for 1 subject.
```
####Header:
```
Authorization: 'Bearer <token>'
```
####Request:
```
{
  timetable_id,
  face_percent: (0 => 100)
}
```
####Response:
```
{
  is_late: (true, false),
  late_min,
  recorded_at
}
```

***

###GET ```attendance/attendance-history```
```
=> Get attendance history of a student, based on following filters:
- class_section (Ex: T1M2, L1M2)
- semester
- status (
    0 - STATUS_NOTYET
    1 - STATUS_PRESENT
    2 - STATUS_LATE
    3 - STATUS_ABSENT
  )
- start_date (Ex: '2016-06-16 00:00:00')
- end_date (Ex: '2016-06-16 00:00:00')
```
###Request: None
###Response:
```
{
  "T1M2": [
    {
      "date": "2016-06-13",
      "lesson_id": "1",
      "class_section": "L2L",
      "component": "LEC",
      "semester": "1",
      "weekday": "MON",
      "status": 0,
      "start_time": "10:00",
      "end_time": "12:00"
    },
    {
      "date": "2016-06-16",
      "lesson_id": "12",
      "class_section": "L2L",
      "component": "LEC",
      "semester": "1",
      "weekday": "THUR",
      "status": 0,
      "start_time": "10:00",
      "end_time": "12:00"
    }
  ],
  "PL23": [
    {
      "date": "2016-06-15",
      "lesson_id": "16",
      "class_section": "PL23",
      "component": "PRA",
      "semester": "1",
      "weekday": "WED",
      "status": 0,
      "start_time": "10:00",
      "end_time": "12:00"
    }
  ]
}
```

***

###GET ```attendance/list-class-section?semester=1```
```
=> Get all class sections in a semester for a student
```
###Request: None
###Response:
```
[
  "L2L",
  "P2L1",
  "T2L1",
  "LL12",
  "PL23"
]
```

***

###GET ```attendance/list-semester```
```
=> Get all semesters for a student
```
###Request: None
###Response:
```
[
  "1",
  "2"
]
```

***

###GET ```timetable/next-days?days=1```
```
=> Get timetable for some next days
```
###Request: None
###Response:
```
{
  "2016-06-30": [
    {
      "lesson_id": "12",
      "start_time": "08:00",
      "end_time": "10:00",
      "class_section": "L2L",
      "component": "LEC",
      "subject_area": "ELECTRO",
      "meeting_pattern": "",
      "weekday": "THUR",
      "location": "Location 1"
    },
    {
      "lesson_id": "10",
      "start_time": "10:00",
      "end_time": "11:00",
      "class_section": "T2L1",
      "component": "TUT",
      "subject_area": "ELECTRO",
      "meeting_pattern": "",
      "weekday": "THUR",
      "location": "Location 1"
    }
  ],
  "2016-07-01": []
}
```
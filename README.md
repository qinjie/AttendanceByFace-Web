#Attendance System
```
'MON' => 0,
'TUES' => 1,
'WED' => 2,
'THUR' => 3,
'FRI' => 4,
'SAT' => 5,
'SUN' => 6,
```
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

###GET ```student/profile```
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
  "id": "1",
  "name": "ADRIAN YOO",
  "gender": null,
  "acad": "AE",
  "uuid": null,
  "user_id": 53,
  "email": "1234@gmail.com"
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

###POST ```user/change-password```
```
=> Change password
```
####Header: None
####Request:
```
{
    oldPassword: '123456',
    newPassword: '111111'
}
```
####Response:
- Success: 200
```
change password successfully
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
=> Request a new password
```
####Header: None
####Request:
```
{
    email: 'abc@mail.com'
}
```
####Response:
- Success: 200
```
reset password successfully
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

###POST ```user/signup-student```
```
=> Sign up new user for a student
```
####Header: None
####Request:
```
{
    username: '1234',
    password: '123456',
    email: 's12345678@connect.np.edu.sg',
    role: 20,
    device_hash: 'ff:ff:ff:ff:ff'
}
```
####Response:
```
{
    token: '3kj2rh3k2rhk2j3hkj42hk43h2kh4j32'
}
```

***

###POST ```user/signup-lecturer```
```
=> Sign up new user for a lecturer
```
####Header: None
####Request:
```
{
    username: '1234',
    password: '123456',
    email: '123@np.edu.sg',
    role: 30,
    device_hash: 'ff:ff:ff:ff:ff'
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
####Request: None
####Response:
```
logout successfully
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
        recorded_at,
        lecturer_name
    }
]
```

***

###GET ```timetable/today-for-lecturer```
```
=> Get all lessons of a lecturer for today, sorted by start time
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
    "lesson_id": "17",
    "subject_area": "AE",
    "class_section": "LM12",
    "component": "LEC",
    "start_time": "09:00",
    "end_time": "10:00",
    "weekday": "WED",
    "meeting_pattern": "",
    "location": "Location 3",
    "name": "Venue 3",
    "number_student": "1"
  },
  {
    "lesson_id": "23",
    "subject_area": "ELECTRO",
    "class_section": "T1M2",
    "component": "TUT",
    "start_time": "13:00",
    "end_time": "15:00",
    "weekday": "WED",
    "meeting_pattern": "EVEN",
    "location": "Location 3",
    "name": "Venue 3",
    "number_student": "3"
  }
]
```

***

###GET ```timetable/one-day?date=2016-11-22```
```
=> Get all lessons for one day, sorted by start time
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
        recorded_at,
        lecturer_name
    }
]
```

***

###GET ```timetable/one-day-for-lecturer?date=2016-07-22```
```
=> Get all lessons of a lecturer for one day, sorted by start time
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
    "lesson_id": "31",
    "subject_area": "MECHANIC",
    "class_section": "T1M2",
    "component": "TUT",
    "start_time": "10:00",
    "end_time": "11:00",
    "weekday": "FRI",
    "meeting_pattern": "",
    "location": "Location 2",
    "name": "Venue 2",
    "number_student": "2"
  },
  {
    "lesson_id": "21",
    "subject_area": "ELECTRIC",
    "class_section": "T1M2",
    "component": "TUT",
    "start_time": "11:00",
    "end_time": "12:00",
    "weekday": "FRI",
    "meeting_pattern": "",
    "location": "Location 3",
    "name": "Venue 3",
    "number_student": "2"
  }
]
```

***

###~~GET ```timetable/week?week=1```~~
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

###~~GET ```timetable/total-week```~~
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

***

###POST ```timetable/take-attendance-beacon```
```
=> Take attendance for IOS app. One student can take attendance only once for 1 subject.
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
  "result": {
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
  },
  "summary": {
    "L2L": {
      "total_lessons": 10,
      "absent_lessons": 0
    },
    "P2L1": {
      "total_lessons": 20,
      "absent_lessons": 0
    },
    "T2L1": {
      "total_lessons": 20,
      "absent_lessons": 0
    },
    "LL12": {
      "total_lessons": 20,
      "absent_lessons": 0
    },
    "PL23": {
      "total_lessons": 5,
      "absent_lessons": 0
    }
  }
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
      "location": "Location 1",
      "lecturer_name": "Zhang Qinjie"
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
      "location": "Location 1",
      "lecturer_name": "Zhang Qinjie"
    }
  ],
  "2016-07-01": []
}
```
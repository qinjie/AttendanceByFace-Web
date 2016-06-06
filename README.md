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
    password: '123456'
}
```
####Response:
```
{
    token: '3kj2rh3k2rhk2j3hkj42hk43h2kh4j32'
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
    role: (10: role user, 20: role student, 30: role teacher)
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
{
    person_id
}
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
{
    face_id
}
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
=> Get all lessons for today
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

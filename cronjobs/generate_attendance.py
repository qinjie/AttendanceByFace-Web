from mysql.connector import MySQLConnection, Error
import time
import datetime
from db import dbConfig

DEFAULT_START_DATE = '2016-06-13'
DEFAULT_END_DATE = '2016-08-21'
SECONDS_IN_DAY = 24 * 60 * 60
SECONDS_IN_WEEK = 7 * 24 * 60 * 60
DEFAULT_SEMESTER = 2

def iter_row(cursor, size=10):
	while True:
		rows = cursor.fetchmany(size)
		if not rows:
			break
		for row in rows:
			yield row

""" Connect to MySQL database """
def connect_db():
	print 'Connecting to MySQL database...'
	conn = MySQLConnection(**dbConfig)
	if conn.is_connected():
		print 'Connection established'
		return conn
	else:
		print 'Connection failed'
		return None


""" Get meeting pattern for today """
def today_meeting_pattern():
	now = datetime.datetime.now()
	currentYear = now.year
	currentMonth = now.month
	currentDay = now.day
	currentYMD = str(currentYear) + '-' + str(currentMonth) + '-' + str(currentDay)
	t1 = int(time.mktime(datetime.datetime.strptime(currentYMD, "%Y-%m-%d").timetuple()))
	t2 = int(time.mktime(datetime.datetime.strptime(DEFAULT_START_DATE, "%Y-%m-%d").timetuple()))
	week = (t1 - t2 + SECONDS_IN_WEEK - 1) / SECONDS_IN_WEEK
	if week % 2 == 0: 
		return 'EVEN'
	else: 
		return 'ODD'


""" Get all lessons of a student """
def get_not_recorded_timetable_today(conn, semester):
	cursor = conn.cursor()
	meeting_pattern = today_meeting_pattern()
	weekday = number_to_weekday(datetime.datetime.now().weekday())
	sql = """SELECT timetable.student_id, timetable.lesson_id 
		FROM timetable JOIN lesson ON timetable.lesson_id = lesson.id 
		LEFT JOIN attendance ON (timetable.lesson_id = attendance.lesson_id AND timetable.student_id = attendance.student_id) 
		WHERE semester = {semester} 
		AND (meeting_pattern = '' OR meeting_pattern = '{meeting_pattern}') 
		AND weekday = '{weekday}' 
		AND attendance.id IS NULL""".format(
		semester = semester,
		meeting_pattern = meeting_pattern,
		weekday = weekday
	)
	cursor.execute(sql)
	for row in iter_row(cursor, 20):
		yield row


""" Convert weekday to number """
def weekday_to_number(weekday):
	map_weekday_number = {
		'MON': 0,
		'TUES': 1,
		'WED': 2,
		'THUR': 3,
		'FRI': 4,
		'SAT': 5,
		'SUN': 6
	}
	return map_weekday_number[weekday]


""" Convert number to weekday """
def number_to_weekday(number):
	map_number_weekday = {
		0: 'MON',
		1: 'TUES',
		2: 'WED',
		3: 'THUR',
		4: 'FRI',
		5: 'SAT',
		6: 'SUN'
	}
	return map_number_weekday[number]


""" Generate absent attendance records for student_id and lesson_id """
def add_absent_attendance(conn, timetable):
	cursor = conn.cursor()
	sql = """INSERT INTO attendance (student_id, lesson_id, is_absent, is_late, late_min) VALUES 
		({student_id}, {lesson_id}, 1, 0, 0)""".format(
		student_id = timetable[0],
		lesson_id = timetable[1]
	)
	cursor.execute(sql)
	conn.commit()


""" Main process """
def run():
	conn = None	
	try:
		conn = connect_db()
		for row in get_not_recorded_timetable_today(conn, DEFAULT_SEMESTER):
			add_absent_attendance(conn, row)

	except Error as e:
		print(e)

	finally:
		conn.close()



if __name__ == '__main__':
	print 'Start cronjob ' + str(datetime.datetime.now())
	run()
	print 'Finish cronjob ' + str(datetime.datetime.now())

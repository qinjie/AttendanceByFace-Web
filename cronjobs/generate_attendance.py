from mysql.connector import MySQLConnection, Error
import time
import datetime
from db import dbConfig

DEFAULT_START_DATE = '2016-06-13'
DEFAULT_END_DATE = '2016-08-21'
SECONDS_IN_DAY = 86400
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


""" Get all lessons of a student """
def get_all_timetable_today(conn, semester):
	cursor = None
	try:
		cursor = conn.cursor()
		sql = """SELECT student_id, lesson_id, weekday,  
			FROM timetable JOIN lesson ON timetable.lesson_id = lesson.id 
			WHERE lesson.semester = {semester}""".format(semester = semester)
		cursor.execute(sql)
		for row in iter_row(cursor, 20):
			yield row
	except:
		pass
	finally:
		cursor.close()	


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


""" Generate attendance records for student_id and lesson_id """
def generate_attendance(conn, timetable, start_date, end_date):
	cursor = None
	try:
		cursor = conn.cursor()
		student_id = timetable[0]
		lesson_id = timetable[1]
		meeting_pattern = timetable[2]
		print '(student_id, lesson_id)', student_id, lesson_id

		start_time = int(time.mktime(
			datetime.datetime.strptime(start_date, "%Y-%m-%d").timetuple()
		))
		end_time = int(time.mktime(
			datetime.datetime.strptime(end_date, "%Y-%m-%d").timetuple()
		))
		count = 0
		iter_week = start_time
		while (iter_week <= end_time):
			++count
			iter_week += SECONDS_IN_DAY
			if meeting_pattern == 'ODD' and count % 2 == 0: continue
			if meeting_pattern == 'EVEN' and count % 2 == 1: continue
			sql = """INSERT INTO attendance (student_id, lesson_id, is_absent, is_late, late_min) 
					VALUES ({student_id}, {lesson_id})""".format(
				student_id = student_id,
				lesson_id = lesson_id
			)
			cursor.execute(sql)
		conn.commit()
	except:
		pass
	finally:
		pass
		# cursor.close()



""" Main process """
def run():
	conn = None	
	try:
		conn = connect_db()
		for row in get_all_timetable(conn, DEFAULT_SEMESTER):
			print 'generate_attendance', row[0], row[1]
			generate_attendance(conn, row, DEFAULT_START_DATE, DEFAULT_END_DATE)

	except Error as e:
		print(e)

	finally:
		conn.close()



if __name__ == '__main__':
	print 'Start cronjob ' + str(datetime.datetime.now())
	run()
	print 'Finish cronjob ' + str(datetime.datetime.now())

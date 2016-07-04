from mysql.connector import MySQLConnection, Error
from time import gmtime, strftime

DEFAULT_START_DATE = '2016-06-13'
DEFAULT_END_DATE = '2016-08-21'
SECONDS_IN_DAY = 86400

def iter_row(cursor, size=10):
	while True:
		rows = cursor.fetchmany(size)
		if not rows:
			break
		for row in rows:
			yield row

""" Connect to MySQL database """
def connect():
	print 'Connecting to MySQL database...'
	conn = MySQLConnection(host='localhost',
												 database='stud_attendance',
												 user='ftpweb',									 password='qw1234er')
	if conn.is_connected():
		print 'Connection established'
		return conn
	else:
		print 'Connection failed'
		return None


""" Get all students in a semester """
def getAllStudentsInSemester(semester):
	pass


def run():
	try:
		conn = connect()
		print 'Running...'		

	except Error as e:
		print(e)

	finally:
		conn.close()



if __name__ == '__main__':
	print 'Start cronjob ' + strftime("%a, %d %b %Y %H:%M:%S", gmtime())
	run()
	print 'Finish cronjob ' + strftime("%a, %d %b %Y %H:%M:%S", gmtime())

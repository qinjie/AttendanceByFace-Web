from mysql.connector import MySQLConnection, Error
from time import gmtime, strftime

STATUS_WAIT_EMAIL_DEVICE = 0
STATUS_WAIT_DEVICE = 1
STATUS_WAIT_EMAIL = 2

STATUS_ACTIVE = 10
STATUS_BLOCKED = 11
STATUS_DELETED = 12


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
												 user='ftpweb',
												 password='qw1234er')
	if conn.is_connected():
		print 'Connection established'
		return conn
	else:
		print 'Connection failed'
		return None


'''
Table user:
0 - id
1 - person_id
2 - face_id
3 - username
4 - auth_key
5 - device_hash
6 - password_hash
7 - email
8 - profileImg
9 - status
10 - role
11 - name
12 - created_at
13 - updated_at
'''
def get_inactive_devices(conn):
	cursor = conn.cursor()
	cursor.execute('SELECT * FROM user WHERE status = %s or status = %s',
		(STATUS_WAIT_DEVICE, STATUS_WAIT_EMAIL_DEVICE))
	for row in iter_row(cursor, 20):
		yield row
	cursor.close()


def activate_devices(list_userId, conn):
	cursor = conn.cursor()
	sql = """UPDATE user SET status = {s1} WHERE id IN ({c}) AND status = {s2}""".format(
		c = ', '.join(['%s'] * len(list_userId)),
		s1 = STATUS_WAIT_EMAIL,
		s2 = STATUS_WAIT_EMAIL_DEVICE)
	cursor.execute(sql, list_userId)
	sql = """UPDATE user SET status = {s1} WHERE id IN ({c}) AND status = {s2}""".format(
		c = ', '.join(['%s'] * len(list_userId)),
		s1 = STATUS_ACTIVE,
		s2 = STATUS_WAIT_DEVICE)
	cursor.execute(sql, list_userId)
	conn.commit()
	print 'Activate_devices %s' % list_userId


def run():
	conn = connect()
	try:
		if (conn):
			list_userId = []
			for row in get_inactive_devices(conn):
				list_userId.append(row[0])
				print row[3], row[9], row[10], row[11]
			print 'Total count: %s' % len(list_userId)
			if len(list_userId) > 0:
				activate_devices(list_userId, conn)

	except Error as e:
		print(e)

	finally:
		conn.close()	



if __name__ == '__main__':
	print 'Start cronjob ' + strftime("%a, %d %b %Y %H:%M:%S", gmtime())
	run()
	print 'Finish cronjob ' + strftime("%a, %d %b %Y %H:%M:%S", gmtime())

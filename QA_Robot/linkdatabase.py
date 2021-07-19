# 将数据库中的数据写入txt文档中，数据库每更新一次都需要运行一次此文件

'''
import pymysql
# 连接数据库并打开library数据库
conn = pymysql.connect(host='182.92.236.190', port=3306, user='root', passwd='123456', db='QA')
# 获取游标对象
cur = conn.cursor()
# 执行SQL语句
cur.execute("SELECT * FROM question_answer")
# 获取执行结果
rows = cur.fetchall()
print("number of records: ", len(rows))
for i in rows:
	print(i)
# 关闭游标对象
cur.close()
# 关闭数据库连接
conn.close()
'''

import pymysql
def get_loan_number(file_txt):
    connect = pymysql.Connect(
        host='182.92.236.190',
        port=3306,
        user='root',
        passwd='123456',
        db='QA',
    )
    print("写入中，请等待……")
    cursor = connect.cursor()
    sql = "select * from qq_aa_y"
    cursor.execute(sql)
    number = cursor.fetchall()
    fp = open(file_txt, "w", encoding='utf-8')
    loan_count = 0
    for loanNumber in number:
        loan_count += 1
        fp.write(str(loanNumber[0]).replace("\r\n","") + "\n" + str(loanNumber[1]).replace("\r\n","") + "\n")
    fp.close()
    cursor.close()
    connect.close()
    print("写入完成,共写入%d条数据！" % loan_count)


if __name__ == "__main__":
    file_txt = r"C:\Users\DELL-PC\Desktop\QA_Robot\question_answer.txt"
    get_loan_number(file_txt)
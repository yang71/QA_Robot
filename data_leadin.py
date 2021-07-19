f = open(r"C:\Users\林娅\Desktop\程序临时\text0\FAQ_教学.txt","r",encoding="utf-8")
f1 = open(r"C:\Users\林娅\Desktop\程序临时\text0\FQ.txt","w",encoding="utf-8")
f2 = open(r"C:\Users\林娅\Desktop\程序临时\text0\FA.txt","w",encoding="utf-8")
txt = f.readlines()
abovetxt = 0  # 上一行的种类： 0空白/注释  1答案   2问题
for t in txt:  # 读取FAQ文本文件
    t = t.strip()  # 移除字符串头尾指定的字符（默认为空格或换行符）或字符序列
    if not t or t.startswith('#'):  # 判断字符串是否以指定字符或子字符串开头
        abovetxt = 0
    elif abovetxt != 2:
        if t.startswith('【问题】'):  # 输入第一个问题
            f1.write(t)
            f1.write('\n')
            f2.write('\n')
            abovetxt = 2
        else:  # 输入答案文本（非第一行的）
            f2.write(t)
            abovetxt = 1
    else:
        if t.startswith('【问题】'):  # 输入问题（非第一行的）
            f1.write(t)
            abovetxt = 2
        else:  # 输入答案文本
            f2.write(t)
            abovetxt = 1
print(txt)
f.close()
f1.close()
f1.close()
#/bin/bash


#
#  导出数据库的 结构和数据， 做成  程序/install   时使用的格式 
#  Author : 青竹丹枫   kyle   316686606@qq.com
#

# 复制文件
cp ../../www/index.php ./rfile/indexphp
cp ../../wwwmobile/index.php ./rfile/indexmobile
cp ../../wwwadmin/index.php ./rfile/indexadmin
cp ../../iframe/config/config.php ./rfile/config
cp ../../app/config/const.php ./rfile/const_app
cp ../../app/m_config/const.php ./rfile/const_mapp
cp ../../admin/config/const.php ./rfile/const_admin

#数据库名称
DATABASE=yixinushopData
#数据库密码
PASSWORD=root

mkdir tableStr
mkdir tableData
# 导出表名
mysql -uroot -p$PASSWORD -e "use $DATABASE; show tables;" > /tmp/3
# 删除第一行，第一行不是表名称
sed -i '1,1d' /tmp/3
# 导表结构
for i in `cat /tmp/3`; do mysqldump -uroot -p$PASSWORD --opt -d $DATABASE $i > ./tableStr/$i.sql ; done;
# 导表数据
for i in `cat /tmp/3`; do mysqldump -uroot -p$PASSWORD --opt -t $DATABASE $i > ./tableData/$i.sql ; done;
# 替换注释
cd ./tableStr
for i in `cat /tmp/3`; do sed -i '/^\-\-/d' ./$i.sql; done;
for i in `cat /tmp/3`; do sed -i '/^\/\*/d' ./$i.sql; done;
# 去掉空行
for i in `cat /tmp/3`; do sed -i '/^$/d' ./$i.sql;  sed -i '1,1d' ./$i.sql;  done;
# 替换注释
cd ../tableData
for i in `cat /tmp/3`; do sed -i '/^\-\-/d' ./$i.sql; done;
for i in `cat /tmp/3`; do sed -i '/^\/\*/d' ./$i.sql; done;
# 去掉空行
for i in `cat /tmp/3`; do sed -i '/^$/d' ./$i.sql; sed -i '1,1d' ./$i.sql; sed -i '2,2d' ./$i.sql;   done;

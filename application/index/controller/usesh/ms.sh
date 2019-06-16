#!/bin/bash
echo '***** start' > /home/wwwroot/mohs/application/index/controller/log
pa=$(pwd)
echo "***** 当前目录：$pa"  >> /home/wwwroot/mohs/application/index/controller/log
export JAVA_HOME=/usr/local/jdk/jdk1.8.0_211
CLASSPATH=$JAVA_HOME/lib/
PATH=$PATH:$JAVA_HOME/bin
export PATH JAVA_HOME CLASSPATH
export MAVEN_HOME=/usr/local/maven/apache-maven-3.6.1
export PATH=$MAVEN_HOME/bin:$PATH
cd $pa
# 文件存储的默认地址目录
cd $1
echo "***** 默认压缩包地址目录：$1"  >> /home/wwwroot/mohs/application/index/controller/log
#要解压的文件名称
zd="${2::-4}"
mkdir -p udir
mkdir -p result
cd udir
echo "***** 项目所在的目录：$gen"  >> /home/wwwroot/mohs/application/index/controller/log
unzip -qo $pa/$1/$2 -d $zd
echo "***** 需要解压的文件：$1/$2"  >> /home/wwwroot/mohs/application/index/controller/log
echo "***** 解压后的地址：$(pwd)/$zd"  >> /home/wwwroot/mohs/application/index/controller/log
#找到pom.xml文件
lp="$(find $(pwd)/$zd -name 'pom.xml')"
echo "***** pom文件所在目录：$lp"  >> /home/wwwroot/mohs/application/index/controller/log
#获得pom.xml文件所在的根目录
ld="${lp::-7}"
#切换到pom.xml文件所在的根目录
echo "***** pom文件所在根目录：$1/$zd$ld"  >> /home/wwwroot/mohs/application/index/controller/log
cd $ld
echo "***** ld目录：$ld"  >> /home/wwwroot/mohs/application/index/controller/log
#进行依赖扫描
mvn dependency:tree -DoutputType=dot | grep \> >$pa/$1/result/${zd}.txt
echo '***** excute mvn' >> /home/wwwroot/mohs/application/index/controller/log

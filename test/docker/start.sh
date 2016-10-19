#/bin/bash

SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

docker_compose()
{
	docker-compose -p rubtsovav/rest-database-export#test -f $DIR/docker-compose.yml $@
}

mysql_available()
{
	# Wait for database to get available
	MYSQL_LOOPS="15"
	i=0
	while ! docker_compose exec mysql mysqladmin ping -h mysql >/dev/null 2>&1; do
	  i=`expr $i + 1`
	  if [ $i -ge $MYSQL_LOOPS ]; then
	  	echo "$(date) - mysql still not reachable, giving up"
	    return 1
	  fi
	  echo "$(date) - waiting mysql..."
	  sleep 5
	done
	return 0
}

docker_compose down
#docker_compose build

docker_compose up -d mysql
if ! mysql_available; then 
	docker_compose down
	exit 1
fi

echo 
echo "-----------"
echo "Test server"
docker_compose run test --testsuite Server

echo 
echo "-----------"
echo "Test compiler"
docker_compose run test --testsuite Compiler

echo 
echo "-----------"
echo "Compile server"
docker_compose run compiler
cp "$DIR/../../server-compiled.php" "$DIR/../resources/server/public/server-compiled.php"
echo copied "$DIR/../../server-compiled.php" to "$DIR/../resources/server/public/server-compiled.php"

echo 
echo "-----------"
echo "Test client with compiled server on php_5.2-apache"
docker_compose up -d php_5.2-apache
docker_compose run wait php_5.2-apache:80 -t 30
docker_compose run -e SERVER_URI=http://php_5.2-apache/server-compiled.php test --testsuite Client

echo 
echo "-----------"
echo "Test client with compiled server on php_5.3-apache"
docker_compose up -d php_5.3-apache
docker_compose run wait php_5.3-apache:80 -t 30
docker_compose run -e SERVER_URI=http://php_5.3-apache/server-compiled.php test --testsuite Client

echo 
echo "-----------"
echo "Test client with compiled server on php_5.6-apache"
docker_compose up -d php_5.6-apache
docker_compose run wait php_5.6-apache:80 -t 30
docker_compose run -e SERVER_URI=http://php_5.6-apache/server-compiled.php test --testsuite Client

echo 
echo "-----------"
echo "Test client with compiled server on php_7.0-apache"
docker_compose up -d php_7.0-apache
docker_compose run wait php_7.0-apache:80 -t 30
docker_compose run -e SERVER_URI=http://php_7.0-apache/server-compiled.php test --testsuite Client


docker_compose down
echo 
echo "-----------"
echo "Test completed"
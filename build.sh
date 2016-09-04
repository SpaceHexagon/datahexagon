#!/bin/bash

output=$1
if [[ "$output" == "" ]]; then
 output="$(dirname $0)/web/app"
else
 output=$(realpath $output)
fi

build() {
 local dist="$1"
 [ -d $dist ] || mkdir -p $dist
 echo "browserify $(pwd)/src/js/app.js"
 browserify -d -e src/js/app.js -t babelify -o "$dist/js/app.js" -v

 #echo "myth $(pwd)/app/src/css/app.css"
 #myth app/src/css/app.css "app/dist/css/app.css"

 #echo "copy echo $(pwd)/public"
 #cp -urv app/public/* "app/data/"

}

build $output

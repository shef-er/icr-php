#!/bin/bash
for arg in "$@"
do
    index=$(echo $arg | cut -f1 -d=)
    val=$(echo $arg | cut -f2 -d=)
    case $index in
        target) TARGET=$val
        ;;
        credentials) CREDENTIALS=$val
        ;;
        # dir) DIR=$val
        # ;;
        *)
    esac
done

touch deploy-log.txt;
if [ "$TARGET" == "backend" ]; then
    git pull $CREDENTIALS 2>&1 | tee deploy-log.txt
    echo "Backend deploy done!";
elif [ "$TARGET" == "frontend" ]; then
    git pull $CREDENTIALS 2>&1 | tee deploy-log.txt
    npm run build && /bin/cp -rf dist/. ../www/public/;
    # npm --prefix $DIR run build && /bin/cp -rf dist/. ../www/public/;
    echo "Frontend deploy done!";
fi
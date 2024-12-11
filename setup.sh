#!/bin/bash

# index.html 파일이 존재하는지 확인하고 삭제
if [ -f "index.html" ]; then
    echo "index.html 파일이 발견되었습니다. 삭제합니다."
    sudo rm -rf index.html
else
    echo "index.html 파일이 존재하지 않습니다."
fi

# uploads 디렉토리가 존재하는지 확인하고 생성
if [ ! -d "uploads" ]; then
    echo "uploads 디렉토리가 존재하지 않습니다. 생성합니다."
    sudo mkdir uploads
else
    echo "uploads 디렉토리가 이미 존재합니다."
fi

# uploads 디렉토리의 소유자와 그룹을 www-data로 변경
echo "uploads 디렉토리의 소유자와 그룹을 www-data:www-data로 변경합니다."
sudo chown www-data:www-data uploads

# 디렉토리 권한 설정 (예: 755)
echo "uploads 디렉토리의 권한을 755로 설정합니다."
sudo chmod 755 uploads

echo "작업이 완료되었습니다."

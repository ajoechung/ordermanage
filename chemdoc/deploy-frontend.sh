#!/bin/bash

# 化工单据管理系统 - 前端部署脚本

echo "========================================="
echo "化工单据管理系统 - 前端部署脚本"
echo "========================================="
echo ""

# 进入前端部署目录
cd /www/wwwroot/chemdoc

echo "[1/5] 拉取最新前端代码..."
git checkout frontend-dist
git pull origin frontend-dist

echo ""
echo "[2/5] 检查部署目录..."
if [ ! -d "/www/wwwroot/chemdoc" ]; then
    echo "错误：部署目录 /www/wwwroot/chemdoc 不存在！"
    exit 1
fi

echo ""
echo "[3/5] 复制前端文件..."
cp -rf /www/wwwroot/chemdoc/* /www/wwwroot/szy.ajoe.cn/

echo ""
echo "[4/5] 设置目录权限..."
chmod -R 755 /www/wwwroot/szy.ajoe.cn/

echo ""
echo "[5/5] 验证部署..."
if [ -f "/www/wwwroot/szy.ajoe.cn/index.html" ]; then
    echo "✅ 前端部署成功！"
    echo ""
    echo "最新的 index.html 内容："
    cat /www/wwwroot/szy.ajoe.cn/index.html
else
    echo "❌ 前端部署失败！"
    exit 1
fi

echo ""
echo "========================================="
echo "前端部署完成！请刷新浏览器查看更新。"
echo "========================================="
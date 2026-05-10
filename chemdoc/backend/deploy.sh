#!/bin/bash

# 化工单据管理系统 - 部署脚本

echo "========================================="
echo "化工单据管理系统 - 后端部署脚本"
echo "========================================="
echo ""

# 进入项目目录
cd /www/wwwroot/chemdoc/backend

echo "[1/5] 拉取最新代码..."
git pull origin main

echo ""
echo "[2/5] 安装Composer依赖..."
composer install --no-dev --optimize-autoloader

echo ""
echo "[3/5] 清除ThinkPHP缓存..."
rm -rf runtime/cache/*
rm -rf runtime/log/*
rm -rf runtime/temp/*

echo ""
echo "[4/5] 重新生成路由缓存..."
php think route:clear
php think route:build

echo ""
echo "[5/5] 设置目录权限..."
chmod -R 755 runtime/
chmod -R 755 public/

echo ""
echo "========================================="
echo "后端部署完成！"
echo "========================================="

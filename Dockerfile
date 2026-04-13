FROM hyperf/hyperf:8.1-alpine-v3.16-swoole

WORKDIR /workspace

# 只安装，不需要手动写 extension=mysqli.so
RUN set -ex \
    && apk update \
    && apk add --no-cache php81-mysqli

# 复制项目文件
COPY . .

# 安装依赖
RUN composer install

# 直接设置目录权限（使用 root）
RUN mkdir -p runtime storage log \
    && chmod -R 755 runtime storage log \
    && chmod +x bin/hyperf.php

EXPOSE 9501

# 不需要 USER 指令，保持 root 用户
CMD ["php", "bin/hyperf.php", "start"]
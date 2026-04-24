import os
import re

EN_US = os.getenv("LANG") != "zh_CN.UTF-8"
API_URL = "https://monojson.com/api/short-link"


HEADER = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36 Edg/132.0.0.0",
}


def is_valid_url(url):
    # 定义 URL 的正则表达式
    pattern = re.compile(
        r"^(https?://)?"  # 协议（http 或 https，可选）
        r"([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}"  # 域名
        r"(:\d+)?"  # 端口号（可选）
        r"(/[^ ]*)?$"  # 路径（可选）
    )
    # 使用正则表达式匹配 URL
    return bool(pattern.match(url))

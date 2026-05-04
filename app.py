import os
import re
import json
import requests
import gradio as gr

EN_US = os.getenv("LANG") != "zh_CN.UTF-8"
API_URL = "https://monojson.com/api/short-link"
HEADER = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36 Edg/132.0.0.0",
}
ZH2EN = {
    "输入长链接": "Input a long URL",
    "输出短链接": "Output short URL",
    "预览短链接": "Preview short URL",
    "将长链接转换为短的、易于共享的链接": "Convert long urls into short, easy-to-share links",
    "状态栏": "Status",
    "短链接生成": "URL Shortner",
}


def _L(zh_txt: str):
    return ZH2EN[zh_txt] if EN_US else zh_txt


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


# outer func
def infer(longUrl: str):
    status = "Success"
    shortUrl = preview = None
    try:
        response = requests.post(API_URL, json={"url": longUrl}, headers=HEADER)
        if response.status_code == 200:
            shortUrl = json.loads(response.text)["shortUrl"]
        else:
            raise ConnectionError(response.text)

        if is_valid_url(shortUrl):
            preview = f"<{shortUrl}>"

    except Exception as e:
        status = f"{e}"

    return status, shortUrl, preview


def main():
    return gr.Interface(
        fn=infer,
        inputs=gr.Textbox(
            label=_L("输入长链接"),
            placeholder=_L("将长链接转换为短的、易于共享的链接"),
        ),
        outputs=[
            gr.Textbox(label=_L("状态栏"), buttons=["copy"]),
            gr.Textbox(label=_L("输出短链接"), buttons=["copy"]),
            gr.Markdown(container=True),
        ],
        flagging_mode="never",
        examples=["https://www.bing.com", "https://www.baidu.com"],
        cache_examples=False,
        title=_L("短链接生成"),
    )


if __name__ == "__main__":
    main().launch(css="#gradio-share-link-button-0 { display: none; }", ssr_mode=False)

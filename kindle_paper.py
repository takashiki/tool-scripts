"""该脚本用于将图片文件转换成kindle壁纸，依赖于Pillow库"""

import os
from PIL import Image

SOURCE_FOLDER = 'r:\\source'
OUTPUT_FOLDER = 'r:\\output'
START_NUMBER = 20
PAPER_SIZE = (600, 800)


def get_source_images(folder):
    return [os.path.join(folder, x) for x in os.listdir(folder)]


def convert_image(file, size, outfile, file_type='PNG'):
    try:
        im = Image.open(file)
        im.thumbnail(size)
        im.save(outfile, file_type)
    except IOError:
        print('cannot convert file', file)
    

if __name__ == '__main__':
    source_images = get_source_images(SOURCE_FOLDER)
    serial = START_NUMBER
    name_str = os.path.join(OUTPUT_FOLDER, 'bg_ss%s.png')
    for image in source_images:
        convert_image(image, PAPER_SIZE, name_str % serial)
        serial += 1

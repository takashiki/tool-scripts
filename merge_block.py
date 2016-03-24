"""该脚本用于合并两台电脑上的cow的blocked域名文件"""

DOMAIN_FILE1 = 'r:\\blocked1.txt'
DOMAIN_FILE2 = 'r:\\blocked2.txt'
OUTPUT_FILE = 'r:\\blocked.txt'


def get_domains_from_file(filename):
    f = open(filename, 'r')
    domains = f.readlines()
    f.close()
    return domains


def save_domains_to_file(filename, domains):
    domains = [x if x.endswith('\n') else x + '\n' for x in domains]
    f = open(filename, 'w')
    f.writelines(domains)
    f.close()


if __name__ == '__main__':
    domains1 = get_domains_from_file(DOMAIN_FILE1)
    domains2 = get_domains_from_file(DOMAIN_FILE2)
    out_domains = list(set(domains1).union(set(domains2)))
    save_domains_to_file(OUTPUT_FILE, out_domains)

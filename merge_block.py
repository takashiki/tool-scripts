"""该脚本用于合并两台电脑上的cow的blocked域名文件"""

DOMAIN_FILE1 = 'r:\\blocked1.txt'
DOMAIN_FILE2 = 'r:\\blocked2.txt'
OUTPUT_FILE = 'r:\\blocked.txt'

def getDomainsFromFile(fileName):
    f = open(fileName, 'r')
    domains = f.readlines()
    f.close()
    return domains

def saveDomainsToFile(fileName, domains):
    domains = [x if x.endswith('\n') else x + '\n' for x in domains]
    f = open(fileName, 'w')
    f.writelines(domains)
    f.close()



if __name__ == '__main__':
    domains1 = getDomainsFromFile(DOMAIN_FILE1)
    domains2 = getDomainsFromFile(DOMAIN_FILE2)
    domains = list(set(domains1).union(set(domains2)))
    saveDomainsToFile(OUTPUT_FILE, domains)
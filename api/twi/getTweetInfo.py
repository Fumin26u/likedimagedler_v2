import sys, json
from time import sleep
from selenium import webdriver

# PHPから送られてきたクエリ文字列から辞書を作成
def makeDictFromQuery(query: str):
    params = query.split(',')
    query = dict()
    for param in params:
        array = param.split('=')
        query[array[0]] = array[1]
    query['isGetFromPreviousPost'] = True if query['isGetFromPreviousPost'] == '1' else False
    return query
# GET_QUERY = makeDictFromQuery(sys.argv[1])
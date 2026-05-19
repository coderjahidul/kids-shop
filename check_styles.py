from bs4 import BeautifulSoup
import hashlib

with open('/home/jahidul/Desktop/kids/category-page.html', 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')
styles = soup.find_all('style')
print(f"Number of style tags in category-page.html: {len(styles)}")

with open('/home/jahidul/Desktop/kids/Home.html', 'r', encoding='utf-8') as f:
    home_html = f.read()
home_soup = BeautifulSoup(home_html, 'html.parser')
home_styles = home_soup.find_all('style')
print(f"Number of style tags in Home.html: {len(home_styles)}")


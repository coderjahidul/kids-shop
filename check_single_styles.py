from bs4 import BeautifulSoup

with open('/home/jahidul/Desktop/kids/single-products-page.html', 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')
styles = soup.find_all('style')
print(f"Number of style tags in single-products-page.html: {len(styles)}")


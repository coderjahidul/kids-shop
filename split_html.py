from bs4 import BeautifulSoup

with open('/home/jahidul/Desktop/kids/Home.html', 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')

app_root = soup.find('app-root')
if app_root:
    print("App-root immediate children tags:")
    for child in app_root.children:
        if child.name:
            print(f"- {child.name} (class: {child.get('class')})")

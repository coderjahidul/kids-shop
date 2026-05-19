from bs4 import BeautifulSoup

with open('/home/jahidul/Desktop/kids/single-products-page.html', 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')
app_root = soup.find('app-root')
if app_root:
    print("App-root children:")
    for child in app_root.children:
        if child.name:
            print(f"- {child.name}")

router_outlet = soup.find('router-outlet')
if router_outlet:
    sibling = router_outlet.find_next_sibling()
    if sibling:
        print(f"Router outlet sibling: {sibling.name}")


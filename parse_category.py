import re
from bs4 import BeautifulSoup

def process_url(url):
    if not url: return url
    if url.startswith('./category-page_files/'):
        return f"<?php echo get_template_directory_uri(); ?>/assets/{url[22:]}"
    if url.startswith('category-page_files/'):
        return f"<?php echo get_template_directory_uri(); ?>/assets/{url[20:]}"
    return url

with open('/home/jahidul/Desktop/kids/category-page.html', 'r', encoding='utf-8') as f:
    html = f.read()

# Regex replace for url('./category-page_files/...')
html = html.replace("url('./category-page_files/", "url('<?php echo get_template_directory_uri(); ?>/assets/")
html = html.replace('url("./category-page_files/', 'url("<?php echo get_template_directory_uri(); ?>/assets/')
html = html.replace("url('category-page_files/", "url('<?php echo get_template_directory_uri(); ?>/assets/")
html = html.replace('url("category-page_files/', 'url("<?php echo get_template_directory_uri(); ?>/assets/')

soup = BeautifulSoup(html, 'html.parser')

# Check and append new styles
with open('style.css', 'r', encoding='utf-8') as f:
    existing_css = f.read()

new_css = ""
styles = soup.find_all('style')
for s in styles:
    if s.string and s.string.strip() not in existing_css:
        new_css += s.string + "\n"

if new_css.strip():
    with open('style.css', 'a', encoding='utf-8') as f:
        f.write("\n/* Category Page Styles */\n" + new_css)

# Update src and href in app-products
app_products = soup.find('app-products')
if app_products:
    for tag in app_products.find_all(['img', 'script', 'source']):
        if tag.has_attr('src'):
            tag['src'] = process_url(tag['src'])
    for tag in app_products.find_all('link'):
        if tag.has_attr('href'):
            tag['href'] = process_url(tag['href'])

    content_str = "<?php get_header(); ?>\n" + str(app_products) + "\n<?php get_footer(); ?>\n"
    content_str = content_str.replace('&lt;?php', '<?php').replace('?&gt;', '?>')

    with open('woocommerce/archive-product.php', 'w', encoding='utf-8') as f:
        f.write(content_str)
    with open('archive.php', 'w', encoding='utf-8') as f:
        f.write(content_str)
    print("Successfully generated archive-product.php and archive.php")
else:
    print("Error: app-products not found")


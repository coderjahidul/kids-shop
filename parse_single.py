import re
from bs4 import BeautifulSoup

def process_url(url):
    if not url: return url
    if url.startswith('./single-products-page_files/'):
        return f"<?php echo get_template_directory_uri(); ?>/assets/{url[29:]}"
    if url.startswith('single-products-page_files/'):
        return f"<?php echo get_template_directory_uri(); ?>/assets/{url[27:]}"
    return url

with open('/home/jahidul/Desktop/kids/single-products-page.html', 'r', encoding='utf-8') as f:
    html = f.read()

# Regex replace for url('./single-products-page_files/...')
html = html.replace("url('./single-products-page_files/", "url('<?php echo get_template_directory_uri(); ?>/assets/")
html = html.replace('url("./single-products-page_files/', 'url("<?php echo get_template_directory_uri(); ?>/assets/')
html = html.replace("url('single-products-page_files/", "url('<?php echo get_template_directory_uri(); ?>/assets/")
html = html.replace('url("single-products-page_files/', 'url("<?php echo get_template_directory_uri(); ?>/assets/')

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
        f.write("\n/* Single Product Page Styles */\n" + new_css)

# Update src and href in app-product-details
app_product_details = soup.find('app-product-details')
if app_product_details:
    for tag in app_product_details.find_all(['img', 'script', 'source']):
        if tag.has_attr('src'):
            tag['src'] = process_url(tag['src'])
    for tag in app_product_details.find_all('link'):
        if tag.has_attr('href'):
            tag['href'] = process_url(tag['href'])

    content_str = "<?php get_header(); ?>\n" + str(app_product_details) + "\n<?php get_footer(); ?>\n"
    content_str = content_str.replace('&lt;?php', '<?php').replace('?&gt;', '?>')

    with open('woocommerce/single-product.php', 'w', encoding='utf-8') as f:
        f.write(content_str)
    with open('single.php', 'w', encoding='utf-8') as f:
        f.write(content_str)
    print("Successfully generated single-product.php and single.php")
else:
    print("Error: app-product-details not found")


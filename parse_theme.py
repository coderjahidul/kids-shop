import re
from bs4 import BeautifulSoup

def process_url(url):
    if not url: return url
    if url.startswith('./Home_files/'):
        return f"<?php echo get_template_directory_uri(); ?>/assets/{url[13:]}"
    if url.startswith('Home_files/'):
        return f"<?php echo get_template_directory_uri(); ?>/assets/{url[11:]}"
    # check for background-image: url(...)
    return url

with open('/home/jahidul/Desktop/kids/Home.html', 'r', encoding='utf-8') as f:
    html = f.read()

# Also do a regex replace for url('./Home_files/...')
html = html.replace("url('./Home_files/", "url('<?php echo get_template_directory_uri(); ?>/assets/")
html = html.replace('url("./Home_files/', 'url("<?php echo get_template_directory_uri(); ?>/assets/')
html = html.replace("url('Home_files/", "url('<?php echo get_template_directory_uri(); ?>/assets/")
html = html.replace('url("Home_files/', 'url("<?php echo get_template_directory_uri(); ?>/assets/')

# Also replace literal ./media/ that might be in css
html = html.replace('url("./media/', 'url("<?php echo get_template_directory_uri(); ?>/assets/media/')

soup = BeautifulSoup(html, 'html.parser')

# Update src and href
for tag in soup.find_all(['img', 'script', 'source']):
    if tag.has_attr('src'):
        tag['src'] = process_url(tag['src'])
for tag in soup.find_all('link'):
    if tag.has_attr('href'):
        tag['href'] = process_url(tag['href'])

# Extract styles
styles = soup.find_all('style')
css_content = "/*\nTheme Name: Kids Shop\nTheme URI: http://example.com/kids-shop\nAuthor: Jahidul Islam\nDescription: A custom kids shop theme\nVersion: 1.0\nText Domain: kids-shop\n*/\n\n"
for s in styles:
    if s.string:
        css_content += s.string + "\n"
    s.decompose()

with open('style.css', 'w', encoding='utf-8') as f:
    f.write(css_content)

# Prepare header.php
html_tag = soup.find('html')
html_attrs = " ".join([f'{k}="{v}"' if isinstance(v, str) else f'{k}="{" ".join(v)}"' for k, v in html_tag.attrs.items()])
head_tag = soup.find('head')
head_content = "".join([str(c) for c in head_tag.contents])
header_content = f"<!DOCTYPE html>\n<html {html_attrs}>\n<head>\n{head_content}\n<?php wp_head(); ?>\n</head>\n<body <?php body_class(); ?>>\n"

app_header = soup.find('app-header-1')
if app_header:
    header_content += str(app_header) + "\n"

with open('header.php', 'w', encoding='utf-8') as f:
    f.write(header_content)

# Prepare footer.php
footer_content = ""
app_footer = soup.find('app-footer')
if app_footer:
    footer_content += str(app_footer) + "\n"

# get remaining scripts in body
body = soup.find('body')
for child in body.children:
    if child.name in ['script', 'noscript'] or (child.name == 'div' and child.get('class') == ['cdk-live-announcer-element', 'cdk-visually-hidden']):
        footer_content += str(child) + "\n"

footer_content += "\n<?php wp_footer(); ?>\n</body>\n</html>"

with open('footer.php', 'w', encoding='utf-8') as f:
    f.write(footer_content)

# Prepare front-page.php
front_page_content = "<?php get_header(); ?>\n"
router_outlet = soup.find('router-outlet')
if router_outlet:
    front_page_content += str(router_outlet) + "\n"
app_home = soup.find('app-home')
if app_home:
    front_page_content += str(app_home) + "\n"

# wait, there's a div with the footer-wrapper if not app-footer.
# let's make sure the footer is captured
if not app_footer:
    # try to find by class footer-wrapper
    div_footer = soup.find('div', class_='footer-wrapper')
    if div_footer:
        with open('footer.php', 'a', encoding='utf-8') as f:
            f.write(str(div_footer) + "\n")
            # need to fix script writing order, let's just append body scripts then wp_footer

front_page_content += "<?php get_footer(); ?>\n"

with open('front-page.php', 'w', encoding='utf-8') as f:
    f.write(front_page_content)


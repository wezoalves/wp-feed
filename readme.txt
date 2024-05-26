### Amazing Feed:

Contributors:      @wezoalves
Tested up to:      6.5
Requires at least: 6.2
Stable tag: 1
License:           GPL-3.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-3.0.html#license-text
Tags:              feed, rss, icaro, xml, publishers, wezoalves

Feed is a comprehensive solution for integrating WordPress content with the ICARO platform. This plugin allows you to create a custom endpoint that lists the latest articles from your site, formatted according to ICARO specifications, ensuring smooth and efficient integration.

#### Features:

**Custom Endpoint:**
- Creates a REST API endpoint in WordPress (`/wp-json/icaro/v1/feed/articles`) to provide articles in the format expected by ICARO.

**Parameter Configuration:**
- Allows URL parameter configuration to define the number of articles to be listed, making it easy to customize the response according to user needs.

**ICARO-Compatible Format:**
- Formats the article response following the RSS 2.0 standard and the detailed specifications in the ICARO Feeds Onboarding Kit, including fields such as title, publication date, author, image, description, and content.

**Content Quality and Compliance:**
- Ensures all articles meet ICARO's quality criteria, such as minimum image size, appropriate title length, and absence of prohibited hyperlinks.

**Detailed Article Information:**
- Includes detailed information about the articles, such as GUID, title, publication date, author, image credit, link, description, content, and thumbnail, ensuring a rich and informative integration.

**Simple Integration:**
- Facilitates the integration of WordPress content with ICARO, allowing partners to quickly send feeds and receive quality feedback to ensure the content is ready for publication.

**Multimedia Support:**
- Supports the inclusion of images, videos, and social media embeds in the article body, following ICARO's formatting guidelines.

**Feedback and Continuous Improvement:**
- Allows generated feeds to be sent for evaluation by the ICARO team, ensuring any issues are quickly identified and resolved for a continuous and efficient publication flow.

#### Usage Example:
1. Install and activate Amazing Feed on your WordPress site.
2. Access the endpoint `/wp-json/icaro/v1/feed/articles` to get the articles in ICARO-compatible format.
3. Send the generated feed for evaluation and integration on the ICARO platform.

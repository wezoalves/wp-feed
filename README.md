# Amazing Feed

**Contributors:** [@WezoAlves](https://www.wezo.com.br)  
**Tested up to:** 6.5  
**Requires at least:** 6.2  
**Stable tag:** 1  
**License:** GPL-3.0-or-later  
**License URI:** https://www.gnu.org/licenses/gpl-3.0.html#license-text  
**Tags:** feed, rss, icaro, tim news, record, r7, xml, publishers, wezoalves, wordpress  

Feed is a comprehensive solution for integrating **WordPress** content with multiple platforms, including **Google News**, **ICARO (Tim News)**, and Portal R7 **_(in development)_**. This plugin creates REST API endpoints to list the latest articles from your site, formatted according to each platform's specifications, ensuring smooth and efficient integration.


![Plugin WordPress Amazing Feed](https://github.com/wezoalves/wp-feed/blob/main/assets/cover.png?raw=true)

## Features

### Custom Endpoints
- Creates REST API endpoints in WordPress to provide articles in formats expected by Google News, ICARO (Tim News), and Portal R7.
- Example endpoints include:
  - `/wp-json/googlenews/v1/feed/articles` for **Google News**
  - `/wp-json/icaro/v1/feed/articles` for **ICARO (Tim News)**

### Parameter Configuration
- Allows URL parameter configuration to define the number of articles to be listed, making it easy to customize the response according to user needs.

The **GoogleNews** supports the following parameters for configuring the feed:

| Parameter  | Type           | Description                                                                 |
| :--------- | :------------- | :-------------------------------------------------------------------------- |
| `num`      | `int`          | Specifies the number of articles to be listed. Default is 30.               |
| `type`     | `string\|array` | Specifies the type of articles to retrieve. Supports single or multiple types. Possible values include 'post', 'video', 'offer', etc. |
| `category` | `string\|array` | Specifies the category of posts to retrieve. Supports single or multiple categories. Categories should be specified by slug. |

### Examples

- **Default** limit 30 articles  
  `/wp-json/googlenews/v1/feed/articles`

- **num** - value between 0 ~ 500  
  `/wp-json/googlenews/v1/feed/articles?num=[limit articles]` 

- **type** - value default post  
  `/wp-json/googlenews/v1/feed/articles?type=post` 

- **type** - multiple values  
  `/wp-json/googlenews/v1/feed/articles?type[]=post&type[]=video` 

- **category** - value eg: news  
  `/wp-json/googlenews/v1/feed/articles?category=news` 

- **category** - multiple values eg: news, local, guide...  
  `/wp-json/googlenews/v1/feed/articles?category[]=news&category[]=local` 

### Platform-Compatible Formats
- Formats the article response following the specifications required by each platform, ensuring compatibility and compliance.
  - Google News format includes elements like title, publication date, author, and keywords.
  - ICARO (Tim News) format follows the RSS 2.0 standard and includes detailed specifications such as title, publication date, author, image, description, and content.

### Content Quality and Compliance
- Ensures all articles meet the quality criteria of each platform, such as minimum image size, appropriate title length, and absence of prohibited hyperlinks.

### Detailed Article Information
- Includes detailed information about the articles, such as GUID, title, publication date, author, image credit, link, description, content, and thumbnail, ensuring a rich and informative integration.

### Simple Integration
- Facilitates the integration of WordPress content with multiple platforms, allowing partners to quickly send feeds and receive quality feedback to ensure the content is ready for publication.

### Multimedia Support
- Supports the inclusion of images, videos, and social media embeds in the article body, following each platform's formatting guidelines.

### Feedback and Continuous Improvement
- Allows generated feeds to be sent for evaluation by platform teams, ensuring any issues are quickly identified and resolved for a continuous and efficient publication flow.

## Usage Example
1. Install and activate Amazing Feed on your WordPress site.
2. Access the endpoint relevant to the platform you are integrating with, such as:
   - `/wp-json/googlenews/v1/feed/articles` for Google News
   - `/wp-json/icaro/v1/feed/articles` for ICARO (Tim News)

3. Send the generated feed for evaluation and integration on the respective platform.

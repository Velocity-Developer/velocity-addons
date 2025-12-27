<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" 
                xmlns:html="http://www.w3.org/TR/REC-html40"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title>XML Sitemap</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<style type="text/css">
					body {
						font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
						color: #333;
						max-width: 75rem;
						margin: 0 auto;
						padding: 2rem;
					}
                    a {
                        color: #0073aa;
                        text-decoration: none;
                    }
                    a:hover {
                        text-decoration: underline;
                    }
					table {
						border: none;
						border-collapse: collapse;
                        width: 100%;
					}
                    th {
                        text-align: left;
                        padding: 1rem .5rem;
                        border-bottom: 1px solid #ccc;
                    }
					td {
						padding: 1rem .5rem;
						border-bottom: 1px solid #eee;
                        font-size: 14px;
					}
                    .header {
                        padding-bottom: 1.5rem;
                        border-bottom: 1px solid #ccc;
                        margin-bottom: 1.5rem;
                    }
                    h1 {
                        margin: 0;
                        font-size: 24px;
                        font-weight: normal;
                    }
                    .desc {
                        color: #666;
                        margin-top: 5px;
                        font-size: 14px;
                    }
				</style>
			</head>
			<body>
				<div class="header">
					<h1>XML Sitemap</h1>
					<p class="desc">
						You can find more information about XML sitemaps on <a href="http://sitemaps.org">sitemaps.org</a>.
					</p>
				</div>
				<div id="content">
					<table cellpadding="5">
						<tr style="border-bottom:1px black solid;">
							<th width="60%">URL</th>
							<th width="15%">Priority</th>
							<th width="15%">Change Frequency</th>
							<th width="25%">Last Modified</th>
						</tr>
						<xsl:for-each select="sitemap:urlset/sitemap:url">
							<tr>
								<td>
									<xsl:variable name="itemURL">
										<xsl:value-of select="sitemap:loc"/>
									</xsl:variable>
									<a href="{$itemURL}">
										<xsl:value-of select="sitemap:loc"/>
									</a>
								</td>
								<td>
									<xsl:value-of select="concat(sitemap:priority*100,'%')"/>
								</td>
								<td>
									<xsl:value-of select="sitemap:changefreq"/>
								</td>
								<td>
									<xsl:value-of select="concat(substring(sitemap:lastmod,0,11),concat(' ', substring(sitemap:lastmod,12,5)))"/>
								</td>
							</tr>
						</xsl:for-each>
					</table>
				</div>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
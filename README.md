# WP Prompts Lib - User Guide

## Overview
WP Prompts Lib is a WordPress plugin designed to create and manage AI prompts with associated metadata, categories, and tags.

## Installation
1. Download the plugin file (`wp-prompts-lib.php`)
2. Upload to your WordPress site's `/wp-content/plugins/` directory
3. Activate the plugin through the WordPress admin panel
4. Go to Settings > Permalinks and click "Save Changes" to refresh permalink structure

## Main Features
- Create and manage AI prompts
- Categorize and tag prompts
- Display prompts in a searchable, filterable grid
- Export prompts to CSV
- Copy prompt content to clipboard

## Creating Prompts

### To Add a New Prompt:
1. In WordPress admin, go to "WP Prompts" > "Add New"
2. Fill in the following fields:
   - Title: Name of your prompt
   - Output/Format: Expected output format
   - Prompt: The main prompt text
   - Contexts (optional): Any contextual information
   - Reasoning Chain (optional): Step-by-step reasoning
   - Best LLM: Recommended language model
3. Add categories and tags as needed
4. Click "Publish" to save

### Field Descriptions:
- **Output/Format**: Specify the desired output format (e.g., "JSON", "Markdown", "List")
- **Prompt**: The main prompt text
- **Contexts**: Additional context or background information
- **Reasoning Chain**: Step-by-step breakdown of the reasoning process
- **Best LLM**: Recommended language model (e.g., "GPT-4", "Claude", "PaLM")

## Displaying Prompts

### Using the Shortcode
Add the prompts display to any page using the shortcode:
```
[display_prompts]
```

### Shortcode Options:
```
[display_prompts posts_per_page="20" category="your-category" orderby="date" order="DESC"]
```

Parameters:
- `posts_per_page`: Number of prompts to display (default: 20)
- `category`: Filter by category slug
- `orderby`: Sort by 'date' or 'title'
- `order`: 'ASC' or 'DESC'

## Features Available on the Prompts Display Page

### Search and Filter:
- Use the search box to find specific prompts
- Filter by category using the dropdown
- Sort by:
  - Newest First
  - Oldest First
  - Title A-Z
  - Title Z-A

### Export Feature:
1. Must be logged in to see the export button
2. Click "Export to CSV" to download all prompts
3. CSV includes:
   - Title
   - Output/Format
   - Prompt
   - Contexts
   - Reasoning Chain
   - Best LLM
   - Categories
   - Tags
   - Date Created

## Individual Prompt View

When viewing a single prompt:
1. All fields are displayed in organized sections
2. "Copy to Clipboard" button available
3. Copying includes:
   - Contexts (if present)
   - Prompt
   - Reasoning Chain (if present)

## Categories and Tags

### Managing Categories:
1. Go to WP Prompts > Categories
2. Add new categories
3. Edit existing categories
4. Categories can have hierarchical structure

### Managing Tags:
1. Add tags when creating/editing prompts
2. Tags are non-hierarchical
3. Use for flexible organization

## Best Practices

### Creating Effective Prompts:
1. Use clear, descriptive titles
2. Fill in all relevant fields
3. Use categories for broad classification
4. Use tags for specific attributes
5. Include detailed context when needed

### Organization:
1. Create a consistent category structure
2. Use meaningful tags
3. Keep prompts updated
4. Document best practices in contexts

## Troubleshooting

### Common Issues:
1. **404 Errors**: 
   - Go to Settings > Permalinks
   - Click "Save Changes"

2. **Export Not Working**:
   - Verify you're logged in
   - Check file permissions
   - Try a smaller export first

3. **Copy Button Not Working**:
   - Check browser permissions
   - Try a different browser

## Security Notes

1. Only logged-in users can:
   - Export prompts to CSV
   - Create new prompts
   - Edit existing prompts

2. Public users can:
   - View prompts
   - Search and filter
   - Copy prompt content

## Support and Updates

For support or questions:
- Contact: https://johnsimmonshypertext.com/get-help-now/
- Documentation: This file is it
- Updates: Check WordPress plugins page for updates

## Technical Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Modern web browser
- JavaScript enabled

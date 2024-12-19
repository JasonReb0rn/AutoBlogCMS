function updateBreadcrumbs() {
    const path = window.location.pathname;
    const breadcrumbsContainer = document.querySelector(".breadcrumbs");
    
    // Early exit if no breadcrumbs container found
    if (!breadcrumbsContainer) return;
  
    // Remove breadcrumbs on homepage
    if (path === "/" || path === "/index.php" || path === "/home.php" || path === "") {
        breadcrumbsContainer.style.display = "none";
        return;
    }
  
    const breadcrumbsCategory = document.getElementById("breadcrumbs-category");
    const breadcrumbsPage = document.getElementById("breadcrumbs-page");
  
    // Handle blog post URLs (new schema)
    if (path.includes("/blog/post/")) {
        // Set category to "Blog" with proper URL
        if (breadcrumbsCategory) {
            breadcrumbsCategory.querySelector("a").textContent = "Blog";
            breadcrumbsCategory.querySelector("a").href = "/blog/page/1";
        }
  
        // Get article title from the page
        if (breadcrumbsPage) {
            const articleTitle = document.querySelector(".blog-post-header h1");
            if (articleTitle) {
                breadcrumbsPage.textContent = articleTitle.textContent.trim();
            } else {
                // Fallback: Get title from URL
                const urlParts = path.split("/");
                const slug = urlParts[urlParts.length - 1];
                const title = slug
                    .split("-")
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                    .join(" ");
                breadcrumbsPage.textContent = title;
            }
        }
    } 
    // Handle blog pagination URLs
    else if (path.includes("/blog/page/")) {
        if (breadcrumbsCategory) {
            breadcrumbsCategory.querySelector("a").textContent = "Blog";
            breadcrumbsCategory.querySelector("a").href = "/blog/page/1";
        }
        
        // Show page number in breadcrumb
        if (breadcrumbsPage) {
            const pageNum = path.split("/").pop();
            breadcrumbsPage.textContent = pageNum === "1" ? "" : `Page ${pageNum}`;
        }
    }
    // Handle other pages
    else {
        // Remove the page breadcrumb for non-article pages
        if (breadcrumbsPage) {
            breadcrumbsPage.remove();
        }
  
        // Set category based on current page
        if (breadcrumbsCategory) {
            let category = "";
            // Extract category from path
            const pathParts = path.split("/").filter(part => part);
            const lastPart = pathParts[pathParts.length - 1];
            
            // Remove .php and transform to title case
            category = lastPart
                .replace(".php", "")
                .replace(/[-_]/g, " ")
                .split(" ")
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(" ");
  
            breadcrumbsCategory.querySelector("a").textContent = category;
            // Keep the original PHP extension for non-blog pages
            breadcrumbsCategory.querySelector("a").href = `/${lastPart}${lastPart.includes('.php') ? '' : '.php'}`;
        }
    }
  
    // Show breadcrumbs container
    breadcrumbsContainer.style.display = "block";
  }
  
  // Run when DOM is loaded
  document.addEventListener("DOMContentLoaded", updateBreadcrumbs);
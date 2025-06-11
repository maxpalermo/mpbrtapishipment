function renderMenu(menuData, targetUl) {
    menuData.forEach((item) => {
        if (item.type === "dropdown") {
            const li = document.createElement("li");
            li.className = "nav-item dropdown";

            const a = document.createElement("a");
            a.className = "nav-link dropdown-toggle d-flex align-items-center";
            a.href = "#";
            a.id = item.id;
            a.setAttribute("role", "button");
            a.setAttribute("data-bs-toggle", "dropdown");
            a.setAttribute("aria-expanded", "false");
            a.innerHTML = `<span class="material-icons me-1">${item.icon}</span> ${item.label}`;

            const ul = document.createElement("ul");
            ul.className = "dropdown-menu";
            ul.setAttribute("aria-labelledby", item.id);

            if (item.children) {
                renderMenu(item.children, ul);
            }

            li.appendChild(a);
            li.appendChild(ul);
            targetUl.appendChild(li);
        } else if (item.type === "link") {
            const li = document.createElement("li");
            const a = document.createElement("a");
            a.className = "dropdown-item d-flex align-items-center";
            a.href = item.href || "#";
            a.innerHTML = `<span class="material-icons me-1">${item.icon}</span> ${item.label}`;
            li.appendChild(a);
            targetUl.appendChild(li);
        } else if (item.type === "divider") {
            const li = document.createElement("li");
            const hr = document.createElement("hr");
            hr.className = "dropdown-divider";
            li.appendChild(hr);
            targetUl.appendChild(li);
        } else if (item.type === "submenu") {
            const li = document.createElement("li");
            li.className = "dropdown-submenu";

            const a = document.createElement("a");
            a.className = "dropdown-item dropdown-toggle d-flex align-items-center";
            a.href = "#";
            a.innerHTML = `<span class="material-icons me-1">${item.icon}</span> ${item.label}`;

            const ul = document.createElement("ul");
            ul.className = "dropdown-menu";

            if (item.children) {
                renderMenu(item.children, ul);
            }

            li.appendChild(a);
            li.appendChild(ul);
            targetUl.appendChild(li);
        }
    });
}

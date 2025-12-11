// User Management Page JavaScript
document.addEventListener("DOMContentLoaded", () => {
    const actionButtons = document.querySelectorAll(".user-actions-btn");
    const actionMenus = document.querySelectorAll(".user-actions-menu");
    const editModal = document.getElementById("edit-user-modal");
    const closeModalBtn = document.getElementById("close-modal-btn");
    const cancelEditBtn = document.getElementById("cancel-edit-btn");
    const saveUserBtn = document.getElementById("save-user-btn");
    const editUserForm = document.getElementById("edit-user-form");
    const modalTitle = document.getElementById("modal-title");
    const modalLoading = document.getElementById("modal-loading");
    const modalBody = document.getElementById("modal-body");
    const modalActions = document.getElementById("modal-actions");
    const passwordGroup = document.getElementById("password-group");

    // Search and filter elements
    const searchInput = document.getElementById("search-input");
    const filterControls = document.getElementById("filter-controls");
    const toggleFiltersBtn = document.getElementById("toggle-filters-btn");
    const addUserBtn = document.getElementById("add-user-btn");
    const filterAgeValue = document.getElementById("filter-age-value");
    const filterHeightValue = document.getElementById("filter-height-value");
    const clearFiltersBtn = document.getElementById("clear-filters-btn");
    const userRows = document.querySelectorAll(".user-row");
    const noResultsMessage = document.getElementById("no-results-message");
    const userCountBadge = document.getElementById("user-count");

    let isEditMode = false; // Track if we're editing or creating

    // Initialize custom selects
    const customSelects = document.querySelectorAll(".custom-select");
    const customSelectValues = {
        "filter-user-type": "all",
        "filter-personalization": "all",
        "filter-age-comparison": "any",
        "filter-height-comparison": "any",
    };

    customSelects.forEach((select) => {
        const selected = select.querySelector(".select-selected");
        const items = select.querySelector(".select-items");
        const name = select.dataset.name;

        // Toggle dropdown
        selected.addEventListener("click", (e) => {
            e.stopPropagation();
            // Close all other selects
            customSelects.forEach((s) => {
                if (s !== select) {
                    s.classList.remove("active");
                    s.querySelector(".select-items").classList.add("hidden");
                }
            });
            select.classList.toggle("active");
            items.classList.toggle("hidden");
        });

        // Handle item selection
        items.querySelectorAll("div").forEach((item) => {
            item.addEventListener("click", (e) => {
                e.stopPropagation();
                const value = item.dataset.value;
                customSelectValues[name] = value;

                // Update selected text
                selected.textContent = item.textContent;

                // Update selected class
                items
                    .querySelectorAll("div")
                    .forEach((i) => i.classList.remove("selected"));
                item.classList.add("selected");

                // Close dropdown
                select.classList.remove("active");
                items.classList.add("hidden");

                // Trigger filter
                applyFilters();
            });
        });
    });

    // Close all selects when clicking outside
    document.addEventListener("click", () => {
        customSelects.forEach((select) => {
            select.classList.remove("active");
            select.querySelector(".select-items").classList.add("hidden");
        });
    });

    // Toggle filters visibility
    toggleFiltersBtn.addEventListener("click", () => {
        filterControls.classList.toggle("hidden");
        toggleFiltersBtn.classList.toggle("active");
    });

    // Add new user button
    addUserBtn.addEventListener("click", () => {
        openAddModal();
    });

    // Toggle menu when clicking the 3 dots button
    actionButtons.forEach((button) => {
        button.addEventListener("click", (e) => {
            e.stopPropagation();
            const userId = button.dataset.userId;
            const userRow = button.closest(".user-row");
            const isCurrentUser = userRow?.dataset.isCurrentUser === "true";

            // Don't open menu for current user
            if (isCurrentUser) {
                return;
            }

            const menu = document.getElementById(`user-actions-${userId}`);

            // Close all other menus and remove menu-active class from all rows
            actionMenus.forEach((m) => {
                if (m !== menu) {
                    m.classList.remove("active");
                    const parentRow = m.closest(".user-row");
                    if (parentRow) {
                        parentRow.classList.remove("menu-active");
                    }
                }
            });

            // Toggle current menu and menu-active class
            menu.classList.toggle("active");
            if (menu.classList.contains("active")) {
                userRow.classList.add("menu-active");
            } else {
                userRow.classList.remove("menu-active");
            }
        });
    });

    // Close menus when clicking outside
    document.addEventListener("click", () => {
        actionMenus.forEach((menu) => {
            menu.classList.remove("active");
            const parentRow = menu.closest(".user-row");
            if (parentRow) {
                parentRow.classList.remove("menu-active");
            }
        });
    });

    // Prevent menu from closing when clicking inside it
    actionMenus.forEach((menu) => {
        menu.addEventListener("click", (e) => {
            e.stopPropagation();
        });
    });

    // Handle edit action
    const editButtons = document.querySelectorAll(".user-action-item.edit");
    editButtons.forEach((button) => {
        button.addEventListener("click", () => {
            const userId = button.dataset.userId;
            const userRow = document.querySelector(
                `.user-row[data-id="${userId}"]`
            );
            const isCurrentUser = userRow?.dataset.isCurrentUser === "true";

            if (isCurrentUser) {
                return;
            }

            openEditModal(userId);
        });
    });

    // Handle delete action
    const deleteButtons = document.querySelectorAll(".user-action-item.delete");
    deleteButtons.forEach((button) => {
        button.addEventListener("click", () => {
            const userId = button.dataset.userId;
            const userRow = document.querySelector(
                `.user-row[data-id="${userId}"]`
            );
            const isCurrentUser = userRow?.dataset.isCurrentUser === "true";

            if (isCurrentUser) {
                return;
            }

            deleteUser(userId);
        });
    });

    // Disable edit/delete buttons and action button for current user
    userRows.forEach((row) => {
        const isCurrentUser = row.dataset.isCurrentUser === "true";
        if (isCurrentUser) {
            const userId = row.dataset.id;
            const actionBtn = document.querySelector(
                `.user-actions-btn[data-user-id="${userId}"]`
            );
            const editBtn = document.querySelector(
                `.user-action-item.edit[data-user-id="${userId}"]`
            );
            const deleteBtn = document.querySelector(
                `.user-action-item.delete[data-user-id="${userId}"]`
            );

            if (actionBtn) {
                actionBtn.classList.add("disabled");
                actionBtn.title =
                    "You cannot edit or delete your own account from here";
            }
            if (editBtn) {
                editBtn.classList.add("disabled");
                editBtn.title = "You cannot edit your own account from here";
            }
            if (deleteBtn) {
                deleteBtn.classList.add("disabled");
                deleteBtn.title = "You cannot delete your own account";
            }
        }
    });

    // Open add modal
    function openAddModal() {
        isEditMode = false;
        modalTitle.textContent = "Add New User";
        saveUserBtn.textContent = "Create User";

        // Show modal immediately
        editModal.classList.add("active");

        // Hide loading, show form
        modalLoading.classList.add("hidden");
        modalBody.classList.remove("hidden");
        modalActions.classList.remove("hidden");

        // Show password field for new users
        passwordGroup.classList.remove("hidden");
        document.getElementById("edit-password").required = true;

        // Enable email field
        document.getElementById("edit-email").disabled = false;

        // Reset form
        editUserForm.reset();
        document.getElementById("edit-user-id").value = "";

        // Setup mutual exclusion between needs_personalization and height/age
        updatePersonalizationFields();
    }

    // Open edit modal and populate with user data
    function openEditModal(userId) {
        isEditMode = true;
        modalTitle.textContent = "Edit User";
        saveUserBtn.textContent = "Save Changes";

        // Show modal immediately with loading state
        editModal.classList.add("active");
        modalLoading.classList.remove("hidden");
        modalBody.classList.add("hidden");
        modalActions.classList.add("hidden");

        // Hide password field for editing (optional password change)
        passwordGroup.classList.remove("hidden");
        document.getElementById("edit-password").required = false;
        document.getElementById("edit-password").placeholder =
            "Leave blank to keep current password";

        // Disable email field (can't change email)
        document.getElementById("edit-email").disabled = true;

        // Fetch user data from the server
        fetch(`/api/users/${userId}`)
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Failed to fetch user data");
                }
                return response.json();
            })
            .then((user) => {
                document.getElementById("edit-user-id").value = user.id;
                document.getElementById("edit-first-name").value =
                    user.first_name || "";
                document.getElementById("edit-last-name").value =
                    user.last_name || "";
                document.getElementById("edit-email").value = user.email || "";
                document.getElementById("edit-password").value = "";
                document.getElementById("edit-height").value =
                    user.height || "";
                document.getElementById("edit-age").value = user.age || "";
                document.getElementById("edit-is-admin").checked =
                    user.is_admin;
                document.getElementById("edit-needs-personalization").checked =
                    user.needs_personalization;

                // Setup mutual exclusion between needs_personalization and height/age
                updatePersonalizationFields();

                // Hide loading, show form
                modalLoading.classList.add("hidden");
                modalBody.classList.remove("hidden");
                modalActions.classList.remove("hidden");
            })
            .catch((error) => {
                console.error("Error fetching user data:", error);
                alert("Failed to load user data. Please try again.");
                closeEditModal();
            });
    }

    // Close edit modal
    function closeEditModal() {
        editModal.classList.remove("active");
        editUserForm.reset();
        modalLoading.classList.add("hidden");
        modalBody.classList.remove("hidden");
        modalActions.classList.remove("hidden");
    }

    closeModalBtn.addEventListener("click", closeEditModal);
    cancelEditBtn.addEventListener("click", closeEditModal);

    // Close modal when clicking outside
    editModal.addEventListener("click", (e) => {
        if (e.target === editModal) {
            closeEditModal();
        }
    });

    // Save user changes or create new user
    saveUserBtn.addEventListener("click", () => {
        const userId = document.getElementById("edit-user-id").value;
        const password = document.getElementById("edit-password").value;

        const userData = {
            first_name: document.getElementById("edit-first-name").value,
            last_name: document.getElementById("edit-last-name").value,
            email: document.getElementById("edit-email").value,
            height: document.getElementById("edit-height").value || null,
            age: document.getElementById("edit-age").value || null,
            is_admin: document.getElementById("edit-is-admin").checked,
            needs_personalization: document.getElementById(
                "edit-needs-personalization"
            ).checked,
        };

        // Add password if provided
        if (password) {
            userData.password = password;
        }

        // Validate required fields
        if (!userData.first_name || !userData.last_name || !userData.email) {
            alert("Please fill in all required fields.");
            return;
        }

        // For new users, password is required
        if (!isEditMode && !password) {
            alert("Password is required for new users.");
            return;
        }

        // Disable the save button while processing
        saveUserBtn.disabled = true;
        saveUserBtn.textContent = isEditMode ? "Saving..." : "Creating...";

        const url = isEditMode ? `/api/users/${userId}` : `/api/users`;
        const method = isEditMode ? "PUT" : "POST";

        fetch(url, {
            method: method,
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
            },
            body: JSON.stringify(userData),
        })
            .then((response) => {
                if (!response.ok) {
                    return response.json().then((err) => {
                        throw new Error(err.message || "Failed to save user");
                    });
                }
                return response.json();
            })
            .then(() => {
                alert(
                    isEditMode
                        ? "User updated successfully!"
                        : "User created successfully!"
                );
                closeEditModal();
                // Reload the page to reflect changes
                window.location.reload();
            })
            .catch((error) => {
                console.error("Error saving user:", error);
                alert(`Failed to save user: ${error.message}`);
            })
            .finally(() => {
                saveUserBtn.disabled = false;
                saveUserBtn.textContent = isEditMode
                    ? "Save Changes"
                    : "Create User";
            });
    });

    // Delete user function
    function deleteUser(userId) {
        if (
            !confirm(
                "Are you sure you want to delete this user? This action cannot be undone."
            )
        ) {
            return;
        }

        fetch(`/api/users/${userId}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Failed to delete user");
                }
                return response.json();
            })
            .then(() => {
                alert("User deleted successfully!");
                // Remove the user row from the DOM
                const userRow = document.querySelector(
                    `.user-row[data-id="${userId}"]`
                );
                if (userRow) {
                    userRow.remove();
                }
                updateUserCount();
            })
            .catch((error) => {
                console.error("Error deleting user:", error);
                alert("Failed to delete user. Please try again.");
            });
    }

    // Search and Filter Functionality
    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const userType = customSelectValues["filter-user-type"];
        const personalization = customSelectValues["filter-personalization"];
        const ageComparison = customSelectValues["filter-age-comparison"];
        const ageValue = parseInt(filterAgeValue.value) || null;
        const heightComparison = customSelectValues["filter-height-comparison"];
        const heightValue = parseInt(filterHeightValue.value) || null;

        let visibleCount = 0;

        userRows.forEach((row) => {
            let show = true;

            // Search by name or email
            if (searchTerm) {
                const name = row.dataset.name || "";
                const email = row.dataset.email || "";
                if (!name.includes(searchTerm) && !email.includes(searchTerm)) {
                    show = false;
                }
            }

            // Filter by user type
            if (userType !== "all") {
                const isAdmin = row.dataset.isAdmin === "true";
                if (userType === "admin" && !isAdmin) show = false;
                if (userType === "regular" && isAdmin) show = false;
            }

            // Filter by personalization status
            if (personalization !== "all") {
                const needsPersonalization =
                    row.dataset.needsPersonalization === "true";
                if (personalization === "completed" && needsPersonalization)
                    show = false;
                if (personalization === "needs" && !needsPersonalization)
                    show = false;
            }

            // Filter by age
            if (ageComparison !== "any" && ageValue !== null) {
                const userAge = parseInt(row.dataset.age) || null;
                if (userAge === null) {
                    show = false;
                } else {
                    if (ageComparison === "equal" && userAge !== ageValue)
                        show = false;
                    if (ageComparison === "above" && userAge <= ageValue)
                        show = false;
                    if (ageComparison === "below" && userAge >= ageValue)
                        show = false;
                }
            }

            // Filter by height
            if (heightComparison !== "any" && heightValue !== null) {
                const userHeight = parseInt(row.dataset.height) || null;
                if (userHeight === null) {
                    show = false;
                } else {
                    if (
                        heightComparison === "equal" &&
                        userHeight !== heightValue
                    )
                        show = false;
                    if (
                        heightComparison === "above" &&
                        userHeight <= heightValue
                    )
                        show = false;
                    if (
                        heightComparison === "below" &&
                        userHeight >= heightValue
                    )
                        show = false;
                }
            }

            // Show or hide the row
            if (show) {
                row.style.display = "";
                visibleCount++;
            } else {
                row.style.display = "none";
            }
        });

        // Show no results message if no users are visible
        if (visibleCount === 0) {
            noResultsMessage.classList.add("active");
        } else {
            noResultsMessage.classList.remove("active");
        }

        updateUserCount(visibleCount);
    }

    // Update user count badge
    function updateUserCount(count = null) {
        if (count === null) {
            count = document.querySelectorAll(
                ".user-row:not([style*='display: none'])"
            ).length;
        }
        const totalCount = userRows.length;
        if (count === totalCount) {
            userCountBadge.textContent = totalCount;
        } else {
            userCountBadge.textContent = `${count} / ${totalCount}`;
        }
    }

    // Clear all filters
    function clearFilters() {
        searchInput.value = "";

        // Reset custom selects
        customSelects.forEach((select) => {
            const name = select.dataset.name;
            const items = select.querySelector(".select-items");
            const firstItem = items.querySelector("div[data-value]");
            const selected = select.querySelector(".select-selected");

            if (firstItem) {
                customSelectValues[name] = firstItem.dataset.value;
                selected.textContent = firstItem.textContent;
                items
                    .querySelectorAll("div")
                    .forEach((i) => i.classList.remove("selected"));
                firstItem.classList.add("selected");
            }
        });

        filterAgeValue.value = "";
        filterAgeValue.disabled = true;
        filterHeightValue.value = "";
        filterHeightValue.disabled = true;
        applyFilters();
    }

    // Enable/disable age and height input based on comparison selection
    function updateAgeInput() {
        const comparison = customSelectValues["filter-age-comparison"];
        filterAgeValue.disabled = comparison === "any";
        if (filterAgeValue.disabled) {
            filterAgeValue.value = "";
        }
        applyFilters();
    }

    function updateHeightInput() {
        const comparison = customSelectValues["filter-height-comparison"];
        filterHeightValue.disabled = comparison === "any";
        if (filterHeightValue.disabled) {
            filterHeightValue.value = "";
        }
        applyFilters();
    }

    // Override the custom select behavior for age and height comparisons
    const ageComparisonSelect = document.querySelector(
        '[data-name="filter-age-comparison"]'
    );
    const heightComparisonSelect = document.querySelector(
        '[data-name="filter-height-comparison"]'
    );

    if (ageComparisonSelect) {
        const items = ageComparisonSelect.querySelectorAll(".select-items div");
        items.forEach((item) => {
            item.addEventListener("click", updateAgeInput);
        });
    }

    if (heightComparisonSelect) {
        const items =
            heightComparisonSelect.querySelectorAll(".select-items div");
        items.forEach((item) => {
            item.addEventListener("click", updateHeightInput);
        });
    }

    // Attach event listeners for filtering
    searchInput.addEventListener("input", applyFilters);
    filterAgeValue.addEventListener("input", applyFilters);
    filterHeightValue.addEventListener("input", applyFilters);
    clearFiltersBtn.addEventListener("click", clearFilters);

    // Handle mutual exclusion between needs_personalization and height/age
    const needsPersonalizationCheckbox = document.getElementById(
        "edit-needs-personalization"
    );
    const heightInput = document.getElementById("edit-height");
    const ageInput = document.getElementById("edit-age");

    function updatePersonalizationFields() {
        const needsPersonalization = needsPersonalizationCheckbox.checked;

        if (needsPersonalization) {
            // If needs personalization is checked, clear and disable height/age
            heightInput.value = "";
            ageInput.value = "";
            heightInput.disabled = true;
            ageInput.disabled = true;
        } else {
            // If not checked, enable height/age fields
            heightInput.disabled = false;
            ageInput.disabled = false;
        }
    }

    function updateNeedsPersonalization() {
        const hasHeight = heightInput.value.trim() !== "";
        const hasAge = ageInput.value.trim() !== "";

        if (hasHeight || hasAge) {
            // If either height or age is filled, uncheck needs personalization
            needsPersonalizationCheckbox.checked = false;
        }
    }

    needsPersonalizationCheckbox.addEventListener(
        "change",
        updatePersonalizationFields
    );
    heightInput.addEventListener("input", updateNeedsPersonalization);
    ageInput.addEventListener("input", updateNeedsPersonalization);
});

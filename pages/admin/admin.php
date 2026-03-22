<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Smooth transitions for sidebar and content */
        .sidebar-transition {
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .content-slide-transition {
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .icon-container {
            width: 24px;
            display: flex;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-text {
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            /* Use max-width instead of width for smoother auto-style transitions */
            transition: opacity 0.3s ease, max-width 0.4s cubic-bezier(0.4, 0, 0.2, 1), margin-left 0.3s ease;
            opacity: 1;
            max-width: 200px; /* Large enough to fit any text */
        }

        /* Collapsed state for text */
        [data-sidebar-collapsed="true"] .sidebar-text {
            opacity: 0;
            max-width: 0;
            margin-left: 0 !important;
            pointer-events: none;
            /* Ensure opacity fades out alongside the width collapse */
            transition: opacity 0.2s ease, max-width 0.4s cubic-bezier(0.4, 0, 0.2, 1), margin-left 0.3s ease;
        }

        .sidebar-item, #toggleContainer {
            display: flex;
            align-items: center;
            padding-left: 28px; 
            width: 100%;
            cursor: pointer;
            border: none;
            background: transparent;
            text-align: left;
        }

        .nav-active {
            background-color: #f3f4f6;
            color: #2563eb;
            border-right: 4px solid #2563eb;
        }

        section {
            display: none;
            min-height: 60vh;
        }
        section.active {
            display: block;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }

        main {
            flex: 1;
            width: 100%; 
        }

        @media (min-width: 768px) {
            main {
                width: calc(100vw - 80px);
            }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 h-16 bg-white border-b border-gray-200 z-50 flex items-center justify-between px-4">
        <div class="flex items-center gap-2">
            <div class="bg-blue-600 p-1.5 rounded-lg">
                <i data-lucide="layers" class="text-white w-6 h-6"></i>
            </div>
            <span class="font-bold text-xl tracking-tight hidden sm:block">NexusCore</span>
        </div>

        <div class="flex items-center gap-4">
            <div class="text-right hidden md:block">
                <p class="text-sm font-semibold leading-none">Alex Rivera</p>
                <p class="text-xs text-gray-500">System Architect</p>
            </div>
            
            <button class="relative p-2 text-gray-500 hover:bg-gray-100 rounded-full transition-colors">
                <i data-lucide="bell" class="w-5 h-5"></i>
                <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
            </button>

            <div class="w-10 h-10 rounded-full bg-blue-100 border-2 border-blue-200 flex items-center justify-center overflow-hidden">
                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Alex" alt="User Profile" class="w-full h-full object-cover">
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <aside id="sidebar" 
           class="fixed left-0 top-16 bottom-12 bg-white border-r border-gray-200 z-40 sidebar-transition w-[80px]"
           data-sidebar-collapsed="true">
        <div class="flex flex-col h-full">
            <div id="toggleContainer" class="py-4 border-b border-gray-100">
                <button id="sidebarToggle" class="p-1 hover:bg-gray-100 rounded-md transition-colors">
                    <i data-lucide="menu" class="w-6 h-6 text-gray-600"></i>
                </button>
            </div>

            <nav class="flex-1 mt-4">
                <button onclick="showPage('dashboard', this)" class="sidebar-item nav-active py-3 text-gray-600 hover:bg-gray-50 transition-all">
                    <div class="icon-container"><i data-lucide="layout-dashboard" class="w-5 h-5"></i></div>
                    <span class="sidebar-text font-medium ml-4">Dashboard</span>
                </button>
                <button onclick="showPage('analytics', this)" class="sidebar-item py-3 text-gray-600 hover:bg-gray-50 transition-all">
                    <div class="icon-container"><i data-lucide="bar-chart-3" class="w-5 h-5"></i></div>
                    <span class="sidebar-text font-medium ml-4">Analytics</span>
                </button>
                <button onclick="showPage('projects', this)" class="sidebar-item py-3 text-gray-600 hover:bg-gray-50 transition-all">
                    <div class="icon-container"><i data-lucide="briefcase" class="w-5 h-5"></i></div>
                    <span class="sidebar-text font-medium ml-4">Projects</span>
                </button>
                <button onclick="showPage('settings', this)" class="sidebar-item py-3 text-gray-600 hover:bg-gray-50 transition-all">
                    <div class="icon-container"><i data-lucide="settings" class="w-5 h-5"></i></div>
                    <span class="sidebar-text font-medium ml-4">Settings</span>
                </button>
            </nav>

            <div class="border-t border-gray-100 py-4">
                <button class="sidebar-item py-2 text-gray-400 hover:text-gray-600 transition-all">
                    <div class="icon-container"><i data-lucide="help-circle" class="w-5 h-5"></i></div>
                    <span class="sidebar-text text-sm ml-4">Support</span>
                </button>
            </div>
        </div>
    </aside>

    <!-- Main Body Content -->
    <main id="mainContent" class="pt-24 pb-16 px-6 content-slide-transition ml-0 md:ml-[80px]">
        
        <section id="dashboard" class="active">
            <h1 class="text-2xl font-bold mb-6">Dashboard Overview</h1>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500 uppercase font-semibold">Total Revenue</p>
                    <h3 class="text-3xl font-bold mt-1">$54,230</h3>
                    <p class="text-green-500 text-sm mt-2 flex items-center gap-1">
                        <i data-lucide="trending-up" class="w-4 h-4"></i> +12.5% vs last month
                    </p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500 uppercase font-semibold">Active Users</p>
                    <h3 class="text-3xl font-bold mt-1">1,284</h3>
                    <p class="text-blue-500 text-sm mt-2 flex items-center gap-1">
                        <i data-lucide="user-check" class="w-4 h-4"></i> 48 currently online
                    </p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500 uppercase font-semibold">Project Completion</p>
                    <h3 class="text-3xl font-bold mt-1">87%</h3>
                    <div class="w-full bg-gray-100 rounded-full h-2 mt-4">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: 87%"></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="analytics">
            <h1 class="text-2xl font-bold mb-6">System Analytics</h1>
            <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 h-96 flex items-center justify-center">
                <div class="text-center">
                    <i data-lucide="pie-chart" class="w-16 h-16 text-gray-200 mx-auto mb-4"></i>
                    <p class="text-gray-500">Real-time data visualization module loading...</p>
                </div>
            </div>
        </section>

        <section id="projects">
            <h1 class="text-2xl font-bold mb-6">Active Projects</h1>
            <div class="space-y-4">
                <div class="bg-white p-4 rounded-lg border border-gray-200 flex items-center justify-between">
                    <div>
                        <h4 class="font-bold">Cloud Migration Phase 1</h4>
                        <p class="text-sm text-gray-500">Target Date: Oct 20, 2024</p>
                    </div>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold uppercase">In Progress</span>
                </div>
                <div class="bg-white p-4 rounded-lg border border-gray-200 flex items-center justify-between">
                    <div>
                        <h4 class="font-bold">Security Audit</h4>
                        <p class="text-sm text-gray-500">Target Date: Nov 05, 2024</p>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold uppercase">Completed</span>
                </div>
            </div>
        </section>

        <section id="settings">
            <h1 class="text-2xl font-bold mb-6">Account Settings</h1>
            <div class="max-w-2xl bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Display Name</label>
                        <input type="text" value="Alex Rivera" class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium">Email Notifications</p>
                            <p class="text-sm text-gray-500">Receive weekly activity reports</p>
                        </div>
                        <div class="w-12 h-6 bg-blue-600 rounded-full flex items-center px-1">
                            <div class="w-4 h-4 bg-white rounded-full ml-auto"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer id="footer" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 h-12 flex items-center justify-between px-6 text-xs text-gray-500 z-50">
        <div>© 2024 NexusCore Infrastructure. All rights reserved.</div>
        <div class="flex gap-4">
            <a href="#" class="hover:text-blue-600">Privacy Policy</a>
            <a href="#" class="hover:text-blue-600">Terms of Service</a>
            <span class="text-gray-300">v2.4.0-stable</span>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        let isCollapsed = true;

        function updateLayout(collapsedState) {
            isCollapsed = collapsedState;
            const isMobile = window.innerWidth < 768;

            if (isCollapsed) {
                sidebar.style.width = isMobile ? '0px' : '80px';
                sidebar.setAttribute('data-sidebar-collapsed', 'true');
                mainContent.style.transform = 'translateX(0)';
            } else {
                const expandedWidth = 256;
                const collapsedWidth = isMobile ? 0 : 80;
                
                sidebar.style.width = expandedWidth + 'px';
                sidebar.setAttribute('data-sidebar-collapsed', 'false');
                
                const slideDistance = expandedWidth - collapsedWidth;
                mainContent.style.transform = `translateX(${slideDistance}px)`;
            }
        }

        // Initialize state
        updateLayout(true);

        sidebarToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            updateLayout(!isCollapsed);
        });

        // Auto-collapse on leave
        sidebar.addEventListener('mouseleave', () => {
            if (!isCollapsed) {
                updateLayout(true);
            }
        });

        function showPage(pageId, element) {
            document.querySelectorAll('section').forEach(sec => sec.classList.remove('active'));
            document.getElementById(pageId).classList.add('active');
            document.querySelectorAll('.sidebar-item').forEach(item => item.classList.remove('nav-active'));
            element.classList.add('nav-active');
        }

        window.addEventListener('resize', () => {
            updateLayout(isCollapsed);
        });
    </script>
</body>
</html>
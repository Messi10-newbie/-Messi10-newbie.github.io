import tkinter as tk
from tkinter import messagebox
from recipe_manager import run_recipe_gui
from shopping_list import run_shopping_list_gui
from ingredient_subs import run_substitute_gui

# ---------------- fake account ----------------
USERS = {
    "user": "1234"
}

attempts = 0
# ---------------- Login ----------------
def login():
    global attempts
    
    username = entry_user.get()
    password = entry_pass.get()

    if username in USERS and USERS[username] == password:
        messagebox.showinfo("Success", "Login Successful")
        show_main_menu()
    else:
        attempts += 1
        messagebox.showerror("Login Failed", "Wrong username or password")
        if attempts >=3:
            messagebox.showerror("Error", "Too Many Ettempts!")
            root.destroy()
            
# ---------------- Main Menu ----------------
def show_main_menu():
    # Clear login screen
    for widget in root.winfo_children():
        widget.destroy()

    root.title("Main Menu")
    root.geometry("400x300")

    tk.Label(root,text="Welcome to Recipe & Nutrition Assistant",font=("Arial", 14)).pack(pady=20)

    tk.Button(root,text="Recipe Manager",width=25,bg="#4CAF50",fg="white" ,command=lambda: run_recipe_gui(root)).pack(pady=10)

    tk.Button(root,text="Ingredient Substitutes",width=25,bg="#2196F3", fg="white",command=lambda: run_substitute_gui(root)).pack(pady=10)

    tk.Button(root,text="Shopping List",width=25, bg="#FF9800", fg="white",command=lambda: run_shopping_list_gui(root)).pack(pady=10)
    
    tk.Button(root,text="Exit",width=25,bg="#f44336",fg="white",command=root.destroy).pack(pady=10)

# ---------------- App Start ----------------
root = tk.Tk()
root.title("Login")
root.geometry("350x250")
root.resizable(False, False)

tk.Label(root, text="Login", font=("Arial", 25, "bold")).pack(pady=20)

tk.Label(root, text="Username").pack()
entry_user = tk.Entry(root)
entry_user.pack()

tk.Label(root, text="Password").pack()
entry_pass = tk.Entry(root, show="*")
entry_pass.pack()

tk.Button(root, text="Login", width=20, command=login).pack(pady=15)

root.mainloop()

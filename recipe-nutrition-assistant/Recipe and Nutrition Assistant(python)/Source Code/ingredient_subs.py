# ingredient_subs_gui.py
import os
import json
import tkinter as tk
from tkinter import ttk, messagebox

FILE = "substitutes.json"

# ------------------- Load & Save -------------------
def load_substitutes():
    if os.path.exists(FILE):
        with open(FILE, "r", encoding="utf-8") as f:
            try:
                return json.load(f)
            except:
                return {}
    return {}

def save_substitutes(subs):
    with open(FILE, "w", encoding="utf-8") as f:
        json.dump(subs, f, indent=4, ensure_ascii=False)

# ------------------- Add Substitute Window -------------------
def open_add_window(parent, subs):
    win = tk.Toplevel(parent)
    win.title("Add Ingredient Substitute")
    win.geometry("350x250")
    win.resizable(False, False)

    tk.Label(win, text="Ingredient name:", font=("Arial", 12)).pack(pady=5)
    ing_entry = tk.Entry(win, width=30)
    ing_entry.pack(pady=5)

    tk.Label(win, text="Substitute:", font=("Arial", 12)).pack(pady=5)
    sub_entry = tk.Entry(win, width=30)
    sub_entry.pack(pady=5)

    tk.Label(win, text="Note (optional):", font=("Arial", 12)).pack(pady=5)
    note_entry = tk.Entry(win, width=30)
    note_entry.pack(pady=5)

    def save_new():
        ing = ing_entry.get().strip().lower()
        sub = sub_entry.get().strip()
        note = note_entry.get().strip()

        if not ing or not sub:
            messagebox.showwarning("Error", "Ingredient name and substitute cannot be empty!", parent=win)
            return

        subs[ing] = {"substitute": sub, "note": note}
        save_substitutes(subs)
        messagebox.showinfo("Success", f"Substitute for '{ing}' has been added!", parent=win)
        win.destroy()

    tk.Button(win, text="Save", font=("Arial", 12), width=15, command=save_new).pack(pady=10)

# ------------------- Find Substitute Window -------------------
def open_find_window(parent, subs):
    win = tk.Toplevel(parent)
    win.title("Find Ingredient Substitute")
    win.geometry("350x200")

    tk.Label(win, text="Enter missing ingredient:", font=("Arial", 12)).pack(pady=10)
    search_entry = tk.Entry(win, width=30)
    search_entry.pack()

    result_label = tk.Label(win, text="", font=("Arial", 12), fg="blue")
    result_label.pack(pady=15)

    def search():
        key = search_entry.get().strip().lower()
        if key in subs:
            text = f"Substitute: {subs[key]['substitute']}"
            if subs[key]['note']:
                text += f"\nNote: {subs[key]['note']}"
            result_label.config(text=text)
        else:
            result_label.config(text="No substitute found for this ingredient.")

    tk.Button(win, text="Search", width=15, font=("Arial", 12), command=search).pack(pady=5)


# ------------------- NEW: View All Substitutes Window -------------------
# ------------------- View All Substitutes Window -------------------
def open_view_all_window(parent, subs):
    win = tk.Toplevel(parent)
    win.title("All Stored Ingredients")
    win.geometry("300x400") # Adjusted geometry for a narrower list
    
    # Check if there are any substitutes to display
    if not subs:
        tk.Label(win, text="No ingredients saved yet!", font=("Arial", 14, "italic")).pack(pady=50)
        return

    # Title adjusted to reflect only ingredients are shown
    tk.Label(win, text="Ingredients with Substitutes:", font=("Arial", 14, "bold")).pack(pady=10)

    # Use a Listbox for the display
    list_frame = tk.Frame(win)
    list_frame.pack(padx=20, pady=5, fill="both", expand=True)

    scrollbar = ttk.Scrollbar(list_frame, orient=tk.VERTICAL)
    # Listbox width reduced since we are only showing the ingredient name
    sub_listbox = tk.Listbox(list_frame, width=30, height=15, yscrollcommand=scrollbar.set, font=("Arial", 11))
    scrollbar.config(command=sub_listbox.yview)

    scrollbar.pack(side=tk.RIGHT, fill=tk.Y)
    sub_listbox.pack(side=tk.LEFT, fill="both", expand=True)

    # Populate the listbox: ONLY show the capitalized ingredient name
    for ing in sorted(subs.keys()):
        # The key 'ing' is the ingredient name (e.g., 'milk', 'butter')
        sub_listbox.insert(tk.END, ing.capitalize())

    # Optional: Add a button to close the window
    tk.Button(win, text="Close", width=15, command=win.destroy).pack(pady=10)


# ------------------- Main Substitute GUI -------------------
def run_substitute_gui(parent):
    subs = load_substitutes()

    root = tk.Toplevel(parent)
    root.title("Ingredient Substitute Assistant")
    root.geometry("400x350") # Increased height to fit new button
    root.resizable(False, False)

    tk.Label(root, text="Ingredient Substitute", font=("Arial", 16, "bold")).pack(pady=20)

    # Existing Buttons
    ttk.Button(root, text="Add Substitute", width=25, command=lambda: open_add_window(root, subs)).pack(pady=5)
    ttk.Button(root, text="Find Substitute", width=25, command=lambda: open_find_window(root, subs)).pack(pady=5)
    
    # NEW Button
    ttk.Button(root, text="View All Substitutes", width=25, command=lambda: open_view_all_window(root, subs)).pack(pady=5)

    ttk.Button(root, text="Exit", width=25,bg="#f44336",fg="white", command=root.destroy).pack(pady=20)

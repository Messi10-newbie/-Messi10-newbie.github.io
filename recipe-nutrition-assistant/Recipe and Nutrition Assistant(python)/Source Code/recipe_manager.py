# recipe_manager_gui.py
import os
import json
import tkinter as tk
from tkinter import simpledialog, messagebox

DATA_FILE = "recipes.json"

# ----------------- Data Handling -----------------
def load_recipes():
    if os.path.exists(DATA_FILE):
        with open(DATA_FILE, "r") as f:
            return json.load(f)
    return {}

def save_recipes(recipes):
    with open(DATA_FILE, "w") as f:
        json.dump(recipes, f, indent=4)

# ----------------- Recipe GUI -----------------
def run_recipe_gui(parent):
    root = tk.Toplevel(parent)
    root.title("🌿 Recipe Organizer & Nutrition")
    root.geometry("900x550")
    root.configure(bg="#f5f5f5")

    recipes = load_recipes()
    weekly_log = []

    # ---------- Frames ----------
    left_frame = tk.Frame(root, bg="#f5f5f5")
    left_frame.grid(row=0, column=0, padx=10, pady=10, sticky="ns")
    right_frame = tk.Frame(root, bg="#f5f5f5")
    right_frame.grid(row=0, column=1, padx=10, pady=10, sticky="ns")
    button_frame = tk.Frame(root, bg="#f5f5f5")
    button_frame.grid(row=0, column=2, padx=10, pady=10, sticky="n")

    # ---------- Recipe List ----------
    tk.Label(left_frame, text="Recipes", font=("Helvetica", 14, "bold"), bg="#f5f5f5").pack()
    recipe_listbox = tk.Listbox(left_frame,width=30,height=20,font=("Helvetica", 12),bd=2,relief="groove",exportselection=False)
    recipe_listbox.pack(pady=5)


    tk.Label(right_frame, text="Ingredients", font=("Helvetica", 14, "bold"), bg="#f5f5f5").pack()
    ingredient_listbox = tk.Listbox(right_frame,width=40,height=20,font=("Helvetica", 12),bd=2,relief="groove",exportselection=False)
    ingredient_listbox.pack(pady=5)

    # ---------- Functions ----------
    def refresh_recipe_list():
        recipe_listbox.delete(0, tk.END)
        for name in recipes.keys():
            recipe_listbox.insert(tk.END, f"{name} ({recipes[name]['category']})")
        ingredient_listbox.delete(0, tk.END)

    def refresh_ingredients(name):
        ingredient_listbox.delete(0, tk.END)
        for ing in recipes[name]["ingredients"]:
            ingredient_listbox.insert(tk.END, f"{ing['name']} - {ing['quantity']} units")

    def add_recipe():
        name = simpledialog.askstring("Add Recipe", "Enter recipe name:", parent=root)
        if not name:
            return
        if name in recipes:
            if not messagebox.askyesno("Overwrite?", f"{name} exists. Overwrite?", parent=root):
                return
        category = simpledialog.askstring("Category", "Enter category (Breakfast/Lunch/Dinner):", parent=root)
        try:
            calories = float(simpledialog.askstring("Calories", "Calories per serving:", parent=root))
        except:
            calories = 0
        recipes[name] = {"category": category, "calories": calories, "ingredients": []}
        save_recipes(recipes)
        refresh_recipe_list()

    def delete_recipe():
        selected = recipe_listbox.curselection()
        if not selected:
            return
        name = recipe_listbox.get(selected[0]).split(" (")[0]
        if messagebox.askyesno("Delete?", f"Delete {name}?", parent=root):
            recipes.pop(name)
            save_recipes(recipes)
            refresh_recipe_list()
            ingredient_listbox.delete(0, tk.END)

    def add_ingredient():
        selected = recipe_listbox.curselection()
        if not selected:
            messagebox.showwarning("Select", "Select a recipe first.", parent=root)
            return
        name = recipe_listbox.get(selected[0]).split(" (")[0]
        ing_name = simpledialog.askstring("Add Ingredient", "Ingredient name:", parent=root)
        if not ing_name:
            return
        try:
            qty = float(simpledialog.askstring("Quantity", "Quantity:", parent=root))
        except:
            qty = 0
        recipes[name]["ingredients"].append({"name": ing_name, "quantity": qty})
        save_recipes(recipes)
        refresh_ingredients(name)

    def delete_ingredient():
        selected_recipe = recipe_listbox.curselection()
        selected_ing = ingredient_listbox.curselection()
        if not selected_recipe:
            messagebox.showwarning("Select", "Please select a recipe first.", parent=root)
            return

        if not selected_ing:
            messagebox.showwarning("Select", "Please select an ingredient to delete.", parent=root)
            return
        recipe_name = recipe_listbox.get(selected_recipe[0]).split(" (")[0]
        ing_index = selected_ing[0]
        recipes[recipe_name]["ingredients"].pop(ing_index)
        save_recipes(recipes)
        refresh_ingredients(recipe_name)

    def record_meal():
        selected = recipe_listbox.curselection()
        if not selected:
            messagebox.showwarning("Select", "Select a recipe first.", parent=root)
            return
        recipe_name = recipe_listbox.get(selected[0]).split(" (")[0]
        try:
            servings = float(simpledialog.askstring("Servings", "Number of servings:", parent=root))
        except:
            servings = 1
        weekly_log.append((recipe_name, servings))
        messagebox.showinfo("Logged", f"Logged {servings} serving(s) of {recipe_name}", parent=root)

    def weekly_summary():
        if not weekly_log:
            messagebox.showinfo("Weekly Summary", "No meals recorded yet.", parent=root)
            return
        summary = {}
        total_calories = 0
        for recipe_name, servings in weekly_log:
            cal = recipes[recipe_name]["calories"] * servings
            summary[recipe_name] = summary.get(recipe_name, 0) + cal
            total_calories += cal
        msg = ""
        for r, c in summary.items():
            msg += f"{r}: {c} kcal\n"
        msg += f"\nTotal calories for the week: {total_calories} kcal"
        messagebox.showinfo("Weekly Summary", msg, parent=root)

    def show_ingredients():
        selected = recipe_listbox.curselection()
        if not selected:
            return
        recipe_name = recipe_listbox.get(selected[0]).split(" (")[0]
        refresh_ingredients(recipe_name)

    # ---------- Buttons ----------
    btn_style = {"width": 20, "font": ("Helvetica", 12), "bg": "#4caf50", "fg": "white", "bd": 2, "relief": "raised"}
    tk.Button(button_frame, text="Add Recipe", command=add_recipe, **btn_style).grid(row=0, column=0, pady=5)
    tk.Button(button_frame, text="Delete Recipe", command=delete_recipe, **btn_style).grid(row=1, column=0, pady=5)
    tk.Button(button_frame, text="Add Ingredient", command=add_ingredient, **btn_style).grid(row=2, column=0, pady=5)
    tk.Button(button_frame, text="Delete Ingredient", command=delete_ingredient, **btn_style).grid(row=3, column=0, pady=5)
    tk.Button(button_frame, text="Show Ingredients", command=show_ingredients, **btn_style).grid(row=4, column=0, pady=5)
    tk.Button(button_frame, text="Record Meal", command=record_meal, **btn_style).grid(row=5, column=0, pady=5)
    tk.Button(button_frame, text="Weekly Summary", command=weekly_summary, **btn_style).grid(row=6, column=0, pady=5)
    tk.Button(button_frame, text="Exit", command=root.destroy, **btn_style).grid(row=7, column=0, pady=5)

    refresh_recipe_list()

import tkinter as tk
from tkinter import ttk, messagebox
import json, os, platform

# ----------------- Constants -----------------
UNITS = ["g", "kg", "ml", "l", "pcs", "cup", "tbsp"]
DATA_FILE = "recipes.json"

# ----------------- Load Recipes -----------------
def load_recipes():
    if os.path.exists(DATA_FILE):
        with open(DATA_FILE, "r") as f:
            data = json.load(f)
            recipes_list = []
            for name, info in data.items():
                recipes_list.append({
                    "name": name,
                    "meal_type": info.get("category", "Dinner"),
                    "ingredients": [
                        {
                            "name": ing.get("name"),
                            "quantity": ing.get("quantity", 0),
                            "unit": ing.get("unit", "pcs")
                        } for ing in info.get("ingredients", [])
                    ]
                })
            return recipes_list
    return []

# ----------------- Scrollable Frame Class -----------------
class ScrollableFrame(ttk.Frame):
    def __init__(self, container, *args, **kwargs):
        super().__init__(container, *args, **kwargs)
        self.canvas = tk.Canvas(self)
        self.scrollbar = ttk.Scrollbar(self, orient="vertical", command=self.canvas.yview)
        self.scrollable_frame = ttk.Frame(self.canvas)

        self.scrollable_frame.bind(
            "<Configure>",
            lambda e: self.canvas.configure(scrollregion=self.canvas.bbox("all"))
        )

        self.canvas.create_window((0, 0), window=self.scrollable_frame, anchor="nw")
        self.canvas.configure(yscrollcommand=self.scrollbar.set)

        self.canvas.pack(side="left", fill="both", expand=True)
        self.scrollbar.pack(side="right", fill="y")

        # Cross-platform mouse wheel
        def _on_mousewheel(event):
            system = platform.system()
            if system == "Windows":
                self.canvas.yview_scroll(-1 * int(event.delta / 120), "units")
            elif system == "Darwin":
                self.canvas.yview_scroll(-1 * int(event.delta), "units")
            else:  # Linux
                if event.num == 4:
                    self.canvas.yview_scroll(-1, "units")
                elif event.num == 5:
                    self.canvas.yview_scroll(1, "units")

        self.canvas.bind("<Enter>", lambda e: self.canvas.bind_all("<MouseWheel>", _on_mousewheel))
        self.canvas.bind("<Leave>", lambda e: self.canvas.unbind_all("<MouseWheel>"))
        self.canvas.bind("<Button-4>", _on_mousewheel)
        self.canvas.bind("<Button-5>", _on_mousewheel)

# ----------------- Shopping List GUI -----------------
def run_shopping_list_gui(parent):
    root = tk.Toplevel(parent)
    root.title("Shopping List Generator")
    root.geometry("900x600")

    scroll_frame = ScrollableFrame(root)
    scroll_frame.pack(fill="both", expand=True)
    frame = scroll_frame.scrollable_frame

    recipes = load_recipes()
    if not recipes:
        messagebox.showwarning("No Recipes", "No recipes found in recipes.json!")

    shopping_list_full = {}
    price_vars = {}
    owned_vars = {}

    # ---------- Step 1 ----------
    frame_recipes = tk.LabelFrame(frame, text="Step 1: Select Recipes")
    frame_recipes.pack(fill='x', pady=5, padx=10)
    recipe_vars = []
    for recipe in recipes:
        var = tk.IntVar()
        tk.Checkbutton(frame_recipes, text=recipe["name"], variable=var).pack(anchor='w')
        recipe_vars.append(var)
    tk.Label(frame_recipes, text="Desired Servings Multiplier (default 1):").pack(anchor='w')
    servings_entry = tk.Entry(frame_recipes)
    servings_entry.insert(0, "1")
    servings_entry.pack(anchor='w') 

    # ---------- Step 2 ----------
    frame_generate = tk.Frame(frame)
    frame_generate.pack(fill='x', pady=5)
    def generate_shopping_list():
        nonlocal shopping_list_full
        shopping_list_full = {}
        if not any(var.get() for var in recipe_vars):
            messagebox.showwarning("No Recipe Selected", "Please select at least one recipe!")
            return
        try:
            factor = float(servings_entry.get())
        except ValueError:
            messagebox.showerror("Error", "Invalid servings multiplier.")
            return
        for i, recipe in enumerate(recipes):
            if recipe_vars[i].get() == 1:
                meal_type = recipe.get("meal_type", "Dinner")
                for ing in recipe.get("ingredients", []):
                    name = ing["name"]
                    qty = ing.get("quantity", 0) * factor
                    unit = ing.get("unit", "pcs")
                    if name in shopping_list_full:
                        shopping_list_full[name]["quantity"] += qty
                    else:
                        shopping_list_full[name] = {
                            "quantity": qty,
                            "unit": unit,
                            "meal_type": meal_type
                        }
        display_price_inputs()
        display_owned_checkboxes()
        shopping_list_text.delete("1.0", tk.END)
        shopping_list_text.insert(tk.END, "Shopping list generated.\n")

    tk.Button(frame_generate, text="Step 2: Generate Shopping List", width=30,bg="#FF9800", fg="white", command=generate_shopping_list).pack()

    # ---------- Step 3 ----------
    frame_price = tk.LabelFrame(frame, text="Step 3: Enter Price per Unit")
    frame_price.pack(fill='x', pady=5, padx=10)
    def display_price_inputs():
        if not shopping_list_full:
            messagebox.showwarning("No Shopping List", "Please generate the shopping list first!")
            return
        for w in frame_price.winfo_children():
            w.destroy()
        price_vars.clear()
        for name, info in shopping_list_full.items():
            row = tk.Frame(frame_price)
            row.pack(anchor='w', pady=2)
            tk.Label(row, text=f"{name}:").pack(side='left')
            var_price = tk.StringVar()
            tk.Entry(row, textvariable=var_price, width=10).pack(side='left', padx=5)
            price_vars[name] = var_price
            # 单位下拉
            unit_var = tk.StringVar(value=info.get("unit", "pcs"))
            unit_combo = ttk.Combobox(row, textvariable=unit_var, values=UNITS, width=5, state="readonly")
            unit_combo.pack(side='left')
            def make_save_unit(name=name, var=unit_var):
                return lambda *args: shopping_list_full[name].update({"unit": var.get()})
            unit_var.trace_add("write", make_save_unit())

    # ---------- Step 4 ----------
    frame_owned = tk.LabelFrame(frame, text="Step 4: Select Ingredients You Already Have")
    frame_owned.pack(fill='x', pady=5, padx=10)
    def display_owned_checkboxes():
        if not shopping_list_full:
            messagebox.showwarning("No Shopping List", "Please generate the shopping list first!")
            return
        for w in frame_owned.winfo_children():
            w.destroy()
        owned_vars.clear()
        for name in shopping_list_full.keys():
            var = tk.IntVar()
            tk.Checkbutton(frame_owned, text=name, variable=var).pack(anchor='w')
            owned_vars[name] = var

    # ---------- Step 5 ----------
    frame_list = tk.LabelFrame(frame, text="Final Shopping List")
    frame_list.pack(fill='both', expand=True, pady=5, padx=10)
    tk.Button(frame_list, text="Step 5: Display Final List", width=30,bg="#FF9800", fg="white", command=lambda: display_final_list()).pack(pady=5)
    shopping_list_text = tk.Text(frame_list, height=15, wrap='none')
    shopping_list_text.pack(side='left', fill='both', expand=True)
    scrollbar_y = tk.Scrollbar(frame_list, orient='vertical', command=shopping_list_text.yview)
    scrollbar_y.pack(side='right', fill='y')
    shopping_list_text.config(yscrollcommand=scrollbar_y.set)
    scrollbar_x = tk.Scrollbar(frame_list, orient='horizontal', command=shopping_list_text.xview)
    scrollbar_x.pack(side='bottom', fill='x')
    shopping_list_text.config(xscrollcommand=scrollbar_x.set)

    def display_final_list():
        if not shopping_list_full:
            messagebox.showwarning("No Shopping List", "Please generate the shopping list first!")
            return
        shopping_list = {k: v.copy() for k, v in shopping_list_full.items()}
        for name, var in owned_vars.items():
            if var.get() == 1 and name in shopping_list:
                shopping_list.pop(name)
        categorized = {}
        for name, info in shopping_list.items():
            categorized.setdefault(info["meal_type"], []).append(info | {"name": name})
        shopping_list_text.delete("1.0", tk.END)
        total_price = 0.0
        for meal, items in categorized.items():
            shopping_list_text.insert(tk.END, f"{meal}:\n")
            for item in items:
                try:
                    price = float(price_vars[item["name"]].get())
                except:
                    price = 0.0
                item_total = item["quantity"] * price
                total_price += item_total
                shopping_list_text.insert(tk.END, f"- {item['name']}: {item['quantity']} {item['unit']} (RM {item_total:.2f})\n")
            shopping_list_text.insert(tk.END, "\n")
        shopping_list_text.insert(tk.END, f"Total Estimated Cost: RM {total_price:.2f}\n")

    # ---------- Exit ----------
    tk.Button(frame, text="Exit", width=30,bg="#f44336",fg="white", command=root.destroy).pack(pady=15)
